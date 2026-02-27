    // v1.0p - Production Release
    function doGet() {
    return HtmlService.createHtmlOutputFromFile('index')
        .setTitle('FLCC Auto-Grader v1.0p')
        .addMetaTag('viewport', 'width=device-width, initial-scale=1')  
        .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);      
    }

    /**
    * Main function to grade the Google Doc
    * @param {string} url - The URL of the Google Doc to grade
    */
    function gradeDocument(url) {
    try {
        // Basic validation
        if (!url.includes('docs.google.com/document')) {
        return { error: "Invalid URL. Please provide a Google Doc URL." };
        }

        var doc = DocumentApp.openByUrl(url);
        var body = doc.getBody();
        var currentDocId = doc.getId(); 
        
        // --- HELPER: Recursively get all paragraphs (including Tables) ---
        function getAllParagraphs(container) {
        var allP = [];
        var numChildren = container.getNumChildren();
        for (var i = 0; i < numChildren; i++) {
            var child = container.getChild(i);
            var type = child.getType();
            
            if (type === DocumentApp.ElementType.PARAGRAPH) {
            allP.push(child.asParagraph());
            } else if (type === DocumentApp.ElementType.LIST_ITEM) {
            allP.push(child.asListItem());
            } else if (type === DocumentApp.ElementType.TABLE) {
            var table = child.asTable();
            for (var r = 0; r < table.getNumRows(); r++) {
                var row = table.getRow(r);
                for (var c = 0; c < row.getNumCells(); c++) {
                allP = allP.concat(getAllParagraphs(row.getCell(c)));
                }
            }
            }
        }
        return allP;
        }

        var paragraphs = getAllParagraphs(body);
        
        var results = {
        docTitle: doc.getName(),
        score: 0,
        totalPossible: 0,
        items: []
        };

        function addResult(criteria, possiblePoints, passed, note) {
        results.totalPossible += possiblePoints;
        if (passed) results.score += possiblePoints;
        results.items.push({
            criteria: criteria,
            points: possiblePoints,
            earned: passed ? possiblePoints : 0,
            status: passed ? "Pass" : "Fail",
            note: note || ""
        });
        }

        // --- 1. Document Name ---
        var docName = doc.getName();
        var nameChanged = !docName.includes("Untitled") && !docName.startsWith("Copy of");
        addResult("Document Name Changed", 5, nameChanged, `Current Name: ${docName}`);

        // --- 2. Background Color ---
        var bgColor = body.getBackgroundColor();
        var isNotWhite = (bgColor !== null && bgColor !== '#ffffff');
        addResult("Background Color Changed", 5, isNotWhite, `Detected Color: ${bgColor || 'Default (White)'}`);

        // --- 3. Margins ---
        var top = doc.getMarginTop();
        var bottom = doc.getMarginBottom();
        var left = doc.getMarginLeft();
        var right = doc.getMarginRight();
        var marginsChanged = (Math.abs(top - 72) > 1 || Math.abs(bottom - 72) > 1 || Math.abs(left - 72) > 1 || Math.abs(right - 72) > 1);
        addResult("Margins Changed", 5, marginsChanged, `Margins: Top: ${Math.round(top)}, Bottom: ${Math.round(bottom)}, Left: ${Math.round(left)}, Right: ${Math.round(right)}`);

        // --- 4. Title Style Used ---
        var hasTitle = false;
        for (var i = 0; i < paragraphs.length; i++) {
        if (paragraphs[i].getHeading() === DocumentApp.ParagraphHeading.TITLE) {
            hasTitle = true;
            break;
        }
        }
        addResult("Title Style Used", 5, hasTitle, hasTitle ? "Found Title style" : "No text found with Title style");

        // --- 5 & 6. Heading 1 & 2 Style Changed ---
        function checkHeadingVisuals(headingType, standardFont, standardSize) {
        var passed = false;
        var log = [];
        var checkedCount = 0;
        var successMsg = "";
        
        for (var i = 0; i < paragraphs.length; i++) {
            var p = paragraphs[i];
            if (p.getHeading() === headingType) {
            var textObj = p.editAsText();
            var content = textObj.getText();
            
            if (!content || content.trim().length === 0) continue;
            checkedCount++;

            var atts = textObj.getAttributes(0);
            var font = atts[DocumentApp.Attribute.FONT_FAMILY];
            var size = atts[DocumentApp.Attribute.FONT_SIZE];
            
            var isExplicitDefault = (font === standardFont && size === standardSize);
            
            var fDisplay = (font === null) ? "Inherited" : font;
            var sDisplay = (size === null) ? "Inherited" : size;
            var shortText = content.length > 15 ? content.substring(0, 15) + "..." : content;
            
            if (!isExplicitDefault) {
                passed = true;
                var fontLabel = (font === null) ? "Inherited" : font;
                var sizeLabel = (size === null) ? "Inherited" : size + "pt";
                
                if (font === null || size === null) {
                fontLabel += "/"+sizeLabel+" (Implies Style Change)";
                successMsg = `'${shortText}': ${fontLabel}`;
                } else {
                successMsg = `'${shortText}': ${fontLabel} ${sizeLabel}`;
                }
                break; 
            } else {
                log.push(`'${shortText}': ${fDisplay}/${sDisplay}pt`);
            }
            }
        }
        
        var note = "";
        var defaultNote = `(Default: ${standardFont} ${standardSize}pt)`;

        if (checkedCount === 0) {
            note = "Fail: No text found with this style.";
        } else if (passed) {
            note = `Pass: Found ${successMsg} ${defaultNote}`;
        } else {
            var logStr = log.join("; ");
            if (logStr.length > 100) logStr = logStr.substring(0, 100) + "...";
            note = `Fail: All checked text matched Default exactly. ${defaultNote}`;
        }
        
        return { passed: passed, note: note };
        }

        var h1Check = checkHeadingVisuals(DocumentApp.ParagraphHeading.HEADING1, "Arial", 20);
        addResult("Heading 1 Style Changed", 5, h1Check.passed, h1Check.note);

        var h2Check = checkHeadingVisuals(DocumentApp.ParagraphHeading.HEADING2, "Arial", 16);
        addResult("Heading 2 Style Changed", 5, h2Check.passed, h2Check.note);


        // --- 7. Page Breaks (6 Points) ---
        var hasPageBreak = false;
        for (var i = 0; i < paragraphs.length; i++) {
        if (paragraphs[i].findElement(DocumentApp.ElementType.PAGE_BREAK)) {
            hasPageBreak = true;
            break;
        }
        }
        if (!hasPageBreak) {
        var numChildren = body.getNumChildren();
        for (var i = 0; i < numChildren; i++) {
            if (body.getChild(i).getType() === DocumentApp.ElementType.PAGE_BREAK) {
            hasPageBreak = true;
            break;
            }
        }
        }
        addResult("Page Breaks", 6, hasPageBreak, hasPageBreak ? "Page break found" : "No hard page break found");


        // --- 8 & 9. Headers & Footers (Safe Sibling Traversal) ---
        var containers = [];
        
        if (doc.getHeader()) containers.push({ type: "Header (Default)", obj: doc.getHeader() });
        if (doc.getFooter()) containers.push({ type: "Footer (Default)", obj: doc.getFooter() });
        
        var siblingsToCheck = [];
        var curr = body.getPreviousSibling();
        while (curr) { siblingsToCheck.push(curr); curr = curr.getPreviousSibling(); }
        curr = body.getNextSibling();
        while (curr) { siblingsToCheck.push(curr); curr = curr.getNextSibling(); }
        
        for (var i = 0; i < siblingsToCheck.length; i++) {
        var s = siblingsToCheck[i];
        var t = s.getType();
        if (t === DocumentApp.ElementType.HEADER_SECTION) {
            containers.push({ type: "Header (Sibling)", obj: s });
        } else if (t === DocumentApp.ElementType.FOOTER_SECTION) {
            containers.push({ type: "Footer (Sibling)", obj: s });
        }
        }

        // 8. Headers & Footers Existence
        var hasHF = false;
        for (var i = 0; i < containers.length; i++) {
        if (containers[i].obj.getNumChildren() > 0 && containers[i].obj.getText().trim().length > 0) {
            hasHF = true;
            break;
        }
        }
        addResult("Headers & Footers", 10, hasHF, hasHF ? "Content found in Header/Footer" : "No content found");


        // 9. Page Numbers
        // Note: Apps Script exposes PAGE_NUMBER fields as UNSUPPORTED via getChild()
        var hasPageNumElement = false;

        for (var c = 0; c < containers.length; c++) {
        var cObj = containers[c].obj;
        var cName = containers[c].type;

        if (!cName.includes("Footer")) continue;

        var pParagraphs = cObj.getParagraphs();
        for (var i = 0; i < pParagraphs.length; i++) {
            var p = pParagraphs[i];
            var numChilds = p.getNumChildren();
            for (var j = 0; j < numChilds; j++) {
            var childType = p.getChild(j).getType();
            if (childType === DocumentApp.ElementType.PAGE_NUMBER ||
                childType === DocumentApp.ElementType.UNSUPPORTED) {
                hasPageNumElement = true;
                break;
            }
            }
            if (hasPageNumElement) break;
        }

        if (hasPageNumElement) break;
        }

        addResult("Page Numbers", 8, hasPageNumElement,
        hasPageNumElement ? "Found Automatic Page Number field." : "No page numbers found in footer.");


        // --- 10. Table of Contents (8 Points) ---
        var hasTOC = false;
        var numChildren = body.getNumChildren();
        for (var i = 0; i < numChildren; i++) {
        if (body.getChild(i).getType() === DocumentApp.ElementType.TABLE_OF_CONTENTS) {
            hasTOC = true;
            break;
        }
        }
        addResult("Table of Contents", 8, hasTOC, hasTOC ? "TOC Found" : "No TOC found");


        // --- 11 & 12. Links (4 Points each) ---
        var hasInternalLink = false;
        var hasExternalLink = false;
        var internalLinkCount = 0;
        var externalLinkCount = 0;
        var allItems = paragraphs;
        
        for (var p = 0; p < allItems.length; p++) {
        var container = allItems[p];
        var textElement = container.editAsText(); 
        var str = textElement.getText();
        if (str.length === 0) continue;
        
        var indices = textElement.getTextAttributeIndices();
        var lastUrl = null; 

        for (var k = 0; k < indices.length; k++) {
            var offset = indices[k];
            var url = textElement.getLinkUrl(offset);
            
            if (url && url !== lastUrl) {
            // Check TOC
            var parent = container.getParent();
            var inTOC = (parent.getType() === DocumentApp.ElementType.TABLE_OF_CONTENTS);
            
            if (!inTOC) {
                var isInternal = false;

                if (url.startsWith('#')) {
                    isInternal = true;
                } 
                else if (url.startsWith('?') && (url.includes('#heading=') || url.includes('#bookmark='))) {
                    isInternal = true;
                }
                else if (url.includes('docs.google.com') && url.includes(currentDocId)) {
                    if (url.includes('#heading=') || url.includes('#bookmark=')) {
                    isInternal = true;
                    }
                }
                
                if (isInternal) {
                hasInternalLink = true;
                internalLinkCount++;
                } else if (url.startsWith('http')) {
                hasExternalLink = true;
                externalLinkCount++;
                }
            }
            }
            lastUrl = url; 
        }
        }
        
        addResult("Link to Heading or Bookmark (Internal)", 4, hasInternalLink, hasInternalLink ? `Found ${internalLinkCount} internal links` : "No internal link found outside TOC");
        addResult("Link to Website (External)", 4, hasExternalLink, hasExternalLink ? `Found ${externalLinkCount} external links` : "No external link found");


        // --- 13. Bookmarks (4 Points) ---
        var bookmarks = doc.getBookmarks();
        var hasBookmark = bookmarks.length > 0;
        var bmNote = hasBookmark ? `Found ${bookmarks.length} manual bookmarks` : "No bookmarks found";
        addResult("Manual Bookmark", 4, hasBookmark, bmNote);


        // --- 14. Images (4 Points) ---
        var inlineImages = body.getImages();
        var positionedCount = 0;
        for (var i = 0; i < paragraphs.length; i++) {
        positionedCount += paragraphs[i].getPositionedImages().length;
        }
        var hfImageCount = 0;
        for (var i = 0; i < containers.length; i++) {
        var cObj = containers[i].obj;
        var cName = containers[i].type;
        hfImageCount += cObj.getImages().length;
        var hfParas = cObj.getParagraphs();
        for (var j = 0; j < hfParas.length; j++) {
            hfImageCount += hfParas[j].getPositionedImages().length;
            // Apps Script cannot expose positioned images in header sections via
            // getPositionedImages(). Their anchor paragraph has 0 children and no
            // text, unlike a normal empty paragraph which always has a TEXT child.
            if (cName.includes("Header") &&
                hfParas[j].getNumChildren() === 0 &&
                hfParas[j].getText() === "") {
            hfImageCount++;
            }
        }
        }
        var totalImages = inlineImages.length + positionedCount + hfImageCount;
        var hasImage = totalImages > 0;
        var imgNote = `Found ${totalImages} images (${inlineImages.length} inline, ${positionedCount} floating`;
        if (hfImageCount > 0) imgNote += `, ${hfImageCount} in header/footer`;
        imgNote += ")";
        addResult("Images", 4, hasImage, imgNote);


        // --- 15. Lists (4 Points) ---
        var hasList = false;
        if (body.getListItems().length > 0) {
        hasList = true;
        }
        addResult("Lists", 4, hasList, hasList ? "List found" : "No lists found");

        // --- 16. PDF (Manual - 4 Points) ---
        addResult("PDF Submitted", 4, false, "MANUAL CHECK REQUIRED: Did they submit a PDF?");

        return results;

    } catch (e) {
        return { error: e.toString() };
    }
    }