let versionCode = `2023-06-08`;

//style properties

let defaultAccentBgColor = '#017d79'; //vibrant color
let defaultAccentTextColor = '#f7f7f7'; //visible within accentColorBgColor
let defaultAccentLinkColor = `lightblue`;

let accentLiteBgColor = '#e3e9f1';//'#f8f9fa'; //neutral but darker

let accentBgColor = defaultAccentBgColor; //vibrant color
let accentTextColor = defaultAccentTextColor; //visible within accentColorBgColor
let accentLinkColor = defaultAccentLinkColor;

let accentDarkBgColor = '#555'; //vibrant and/or dark color
let accentDarkTextColor = '#e9e9e9'; //visible within accentDarkBgColor
let accentDarkLinkColor = `lightblue`;

let codeBgColor = `#f5f2f0`;

let headingAlign = "center";
let showEmbeds = false;

let rowMargins = "mx-1 mb-4"; //https://getbootstrap.com/docs/4.0/utilities/spacing/
//let rowMarginsEm = "margin: 0 .5em 1em .5em";
//image file prefix
//let imgPrefix = 'asullivan.csc.flcc.edu/lms-content/pics/';

//handles into HTML elements of preview page
const $markdownTextArea = document.getElementById("originalText");
$markdownTextArea.addEventListener("input", handleNewText);

const $previewTextArea = document.getElementById("previewArea");
const $htmlTextArea = document.getElementById("htmlResultText");

const $fileButton = document.getElementById("btn-input-file");
$fileButton.addEventListener("change", loadFile);

const $btnHeadings = document.getElementById("btn-toggle-headings");
$btnHeadings.addEventListener("click", toggleHeadings);

const $selectResultType = document.getElementById("select-result");
$selectResultType.addEventListener("change", changeResultType);

const $selectExample = document.getElementById("select-example");
$selectExample.addEventListener("change", changeExample);

const $selectColorTheme = document.getElementById("select-color-theme");
$selectColorTheme.addEventListener("change", changeColorTheme);

const $selectMetaCopy = document.getElementById("select-meta-copy");
const $btnCopyMeta = document.getElementById("btn-copy-meta");
$btnCopyMeta.addEventListener("click", copyMetaContent);

//elements (tags) for converted html 
const tagsTop = `<!DOCTYPE html>
<html lang="en"><head>
   <!-- Required meta tags -->
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="bootstrap.min.css">
   <!-- Font Awesome CSS -->
   <link rel="stylesheet" href="all.min.css">
   <!-- prism.min.css for code linting -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.28.0/themes/prism.min.css">
   <!-- Template CSS -->
   <link rel="stylesheet" href="styles.min.css">
   

const tagsTopClosing =`
   <title>Page</title>
</head>
<body>
<div class="container-fluid">\n`;

const tagsBottom = `</div>
<script src="jquery-3.3.1.slim.min.js"></script>
<script src="popper.min.js"></script>
<script src="bootstrap.min.js"></script>
<!-- Template JavaScript -->
<script src="scripts.min.js"></script>
</body></html>\n`;

const tagsTopStandalone = `<!DOCTYPE html>
<html lang="en"><head>
   <!-- Required meta tags -->
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   
   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="https://cdn.usebootstrap.com/bootstrap/4.3.1/css/bootstrap.min.css">
   <!-- Font Awesome CSS -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.9.0/css/all.min.css">
   <!-- prism.min.css for code linting -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.28.0/themes/prism.min.css">
   <!-- Template CSS -->
   <link rel="stylesheet" href="styles.css">
   <link rel="stylesheet" href="custom.css">
   
   <link rel="icon" type="image/x-icon" href="favicon.ico">
   

   `;

   const tagsBottomStandalone = `</div>
   <script src="jquery.slim.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.5/umd/popper.min.js"></script>
   <script src="https://cdn.usebootstrap.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
   </body></html>\n`;

let tagsStyle = `
   <style>
   </style>`;

const columnTextStyle = 
    `columns: 50ch; `
    + `padding: 0 0 .5rem 0; `
    + `text-align: top`;

//handles into HTML elements of preview page

//globals
let convertedHTML = "";
let markdownMemory = "";
let metaItems = [];

//marked.js is a markdown to HTML converter
//We are extending it to add custom features to markdown and
//add in styles to work well with Brightspace editor content

marked.setOptions( {
    renderer: new marked.Renderer(),
    //tokenizer: new marked.Tokenizer(),
    highlight: (code, lang) => {
        if (Prism.languages[lang]) {
            return Prism.highlight(code, Prism.languages[lang], lang);
        } else {
            return code;
        }
        /*
        //highlight.js
        //const hljs = require('highlight.min.js');
        const validLanguage = hljs.getLanguage(language) ? language : 'plaintext';
        return hljs.highlight(validLanguage, code).value;
        */
    },
    pedantic: false,
    gfm: true,
    breaks: false,
    sanitize: false,
    smartLists: true,
    smartypants: false,
    xhtml: false
});

let codeId = 0; //unique number for each code highlighted (for copy button)

//Extensions added to marked.js renderer
//Compare to source: https://github.com/markedjs/marked/blob/master/src/Renderer.js 
//These are for rendering in Brightspace using Bootstrap 4.3.1: 
//Bootstrap 4.6 documentation: https://getbootstrap.com/docs/4.6/getting-started/introduction/
const rendererFile = {
    link(href, title, text) {
        let link = marked.Renderer.prototype.link.call(this, href, title, text);
        
        //[click to download it](google-download:https://googlesharelink)
        link = getGooglizedLinkURL(href, link);
        
        return link.replace(`<a`,`<a target='_blank'`);
    },

    image(href, title, text) {
        let image = marked.Renderer.prototype.image.call(this, href, title, text);
        let roundClass = 'rounded'; //default is slightly rounded corners
        let alignClass = 'mx-auto d-block'; //default is centered block (margin x auto)
        let widthStyle = ``;
        let borderStyle = ``;
        if (text != null) {
            if (text.indexOf('^') == 0) {
                let altLengthWithCode = image.length;
                //remove the code, including the ^ from the alt text https://regex101.com/r/zDZBhq/5
                image = image.replace(/alt="\^[\+,\-,o,b]*(w[0-9][0-9])*[\+,\-,o,b]*[ ^]*/i, `alt="`);
                let codeLength = altLengthWithCode - image.length;
                
                let roundIndex = text.indexOf('o');
                let leftIndex = text.indexOf('-');
                let rightIndex = text.indexOf('+');
                let widthIndex = text.indexOf('w');
                let borderIndex = text.indexOf('b');

                //if there is an o in the code, roundClass is rounded-circle
                if (roundIndex >= 1 && roundIndex < codeLength) {
                    roundClass = 'rounded-circle';
                }

                // - means float left (mr-3 means standard margin on right)
                if (leftIndex >= 1 && leftIndex < codeLength) {               
                    alignClass = `float-left mr-3`;        
                }
                // + means float right (ml-3 means standard margin on left)
                else if (rightIndex >= 1 && rightIndex < codeLength) {
                    alignClass = `float-right ml-3`;
                }

                //w and a number between 1 and 9 inclusive means
                // to change the max-width to be 10% to 90%
                if (widthIndex >= 1 && widthIndex < codeLength-2) {
                    let wPercent = +text[widthIndex+1] * 10 + +text[widthIndex+2];      
                    widthStyle = `max-width: ${wPercent}%; `;
                }

                //b means add a 1 pixel black border
                if (borderIndex >= 1 && borderIndex < codeLength) {
                    borderStyle = `border: 1px solid black; `;
                }
            }
        }
        
        //![test google drive image](google:https://drive.google.com/file/d/1CFZgcNtOuE_ETgvTyoez7JgrGz9p_Qql/view?usp=share_link)
        //![test google thumbnail](google-thumb:https://docs.google.com/drawings/d/1PL6Id2pRVH_rWgbAY9QjuqCrsNgYz6j5OYKZ2alLfis/edit?usp=sharing)
        image = getGooglizedImageElement(href, image);

        return image.replace("<img", 
            `<img class="img-fluid ${roundClass} ${alignClass}" style="margin-bottom: .5em; ${widthStyle} ${borderStyle}"`);
    },

    list(body, ordered, start) {
        if (ordered) {
            const startatt = start !== 1 ? ` start="${start}"` : '';
            if (body.indexOf('^large^') >= 0) {
                const newBody = body.replace('^large^', '');
                return `<ol class="large-number"${startatt}>\n${newBody}</ol>\n`;
            } else if (body.indexOf('^box-alpha^') >= 0) {
                const newBody = body.replace('^box-alpha^', '');
                return `<ol class="box-alpha"${startatt}>\n${newBody}</ol>\n`;
            } else if (body.indexOf('^box^') >= 0) {
                const newBody = body.replace('^box^', '');
                return `<ol class="box-number"${startatt}>\n${newBody}</ol>\n`;
            } else {
                return `<ol${startatt}>\n${body}</ol>\n`;
            }
        } else {
            // if (body.indexOf('^icon^') >= 0) {
            //     const newBody = body.replace('^icon^', '');
            //     return `<ul class="list-icon">\n${newBody}</ul>\n`;
            // } else {
                return `<ul>\n${body}</ul>\n`;
           // }
        }
    },

    checkbox(checked) {
        //only change is leaving off the disabled="" in style so students
        //can check the boxes (no other effects and no state stored)
        return '<input '
            + (checked ? 'checked=""' : '')
            + 'type="checkbox" '
            + 'style="font-size: 1em" '
            + (this.options.xhtml ? ' /' : '')
            + '> ';
    },        

    code(code, infostring, escaped) {
        let useCopy = infostring.substring(infostring.length-5).toLowerCase() == "^copy";
        let langstring = useCopy ? infostring.substring(0, infostring.length-5) : infostring;
        const lang = (langstring || '').match(/\S*/)[0];
        const originalCode = code;
        if (this.options.highlight) {
            const out = this.options.highlight(code, lang);
            if (out != null && out !== code) {
            escaped = true;
            code = out;
            }
        }

        if (!lang) {
            if (useCopy) {
                return "<script>const code" + codeId + "= `" + originalCode + "\n`;</script>" +
`<button style="position: relative; display: inline-block;" onclick="navigator.clipboard.writeText(code${codeId++} );"> Copy this code </button>
<pre contendeditable="false" class="${this.options.langPrefix+'none'}">
${code}
</pre>
`;
            } else {
                return `<pre contendeditable="false" class="${this.options.langPrefix+'none'}">
${code}
</pre>`;
            }
        }
        if (useCopy) {
            return "<script>const code" + codeId + "= `" + originalCode + "\n`;</script>" +
`<button style="position: relative; display: inline-block;" onclick="navigator.clipboard.writeText(code${codeId++} );"> Copy this code </button>
<pre contendeditable="false" class="${this.options.langPrefix}${lang}">
${code}
</pre>
`;
        } else {
            return `<pre contendeditable="false" class="${this.options.langPrefix}${lang}">
${code}
</pre>`;
        }
    },

    table(header, body) {
        let captionText = '';
        if (body) {
            const captionIndex = body.indexOf(`^cap `);
            if (captionIndex >= 0) {
                const captionEndIndex = body.indexOf(`^`, captionIndex+5);
                captionText = body.substring(captionIndex+5, captionEndIndex);
                body = body.replace(`^cap ${captionText}^`, '');
            }  
            body = `<tbody>${body}</tbody>`;
        }
        return '<div class="table-responsive"><table class="table table-bordered">\n'
        + '<caption>' + captionText + '</caption>'
        + '<thead>\n'
        + header
        + '</thead>\n'
        + body
        + '</table></div>\n';
    },

    //em emphasis/italic is being used for custom spans like icons and keyboard
    em(text) {
        const iconKey = '^icon';
        const keyKey = '^key';
        let i = text.indexOf(iconKey);
        if (i < 0) {
            i = text.indexOf(keyKey);
            if (i < 0) {
                //basic italic
                return `<em>${text}</em>`;
            }
            //key
            else {
                let keyName = text.substring(i+keyKey.length+1);
                if (keyName) {
                    return `<kbd>${keyName}</kbd>`;
                }
                else {
                    return `<span style="color: red">key not named</span>`;
                }
            }
        } else {
            //icon
            let iconName = text.substring(i+iconKey.length+1);
            if (iconName) {
                let suffix = 's';
                if (iconName.indexOf('b-') == 0) {
                    iconName = iconName.substring(2);
                    suffix = 'b';
                }
                return `<i class="fa${suffix} fa-${iconName}"></i>`;
            }
            else {
                return `<span style="color: red">icon not named</span>`;
            } 
        } 
    },

    paragraph(text) {
        //we are hijacking the paragraph renderer here
        //check for specific starting tokens and do something with them
        //this could be extended with new tokenizers I believe, but for now this works

        //Responsive YouTube iframe
        //Usage: Start paragraph with ^youtube and paste embed <iframe> code from youtube one space after it.

        textLower = text.toLowerCase();
        const prefix = "^";
        /* columns          : responsive divs horizontally aligned as columns
            ^= = =           (deprectated) start 3 column div and begin first column
            ^= =             start multi-column row div and begin first column
            ^=^              start row with 1 centered column at reading width
            ^===             column break (begin next column)
            ^=               end column div (return to normal single column)
            ^=t=             start multi-column text area
            
            accents          : add at end of column start key or break ^=== ,,
            ,,,,             accent level Outline - outlined with accent color
            ,,,              accent level Dark - strong (invert)
            ,,               accent level Color - colorful
            ,                accent level Lite - subtle separation

            color themes
            colors           ^colors bg:#00000 text:#ffffff link:#dddddd
        */
        const colPrefix = "=";
        const colTextStartKey = "t=";        // ^=t=
        const col2StartKey = " =";           // ^= =
        const col3StartKey = " = =";         // ^= = = deprecated
        const col1CenterStartKey = "^";      // ^=^
        const colBreakKey = "==";            // ^===
        const colEndKey = "";                // ^=
        const sectionStartKey = "---";
        const sectionEndKey = "-";
        const accentOutlineKey = ",,,,";
        const accentDarkKey = ",,,";
        const accentColorKey = ",,";
        const accentLiteKey = ",";
        const youTubeKey = "youtube";        // ^youtube
        const bannerKey  = "^";              // ^^
        const accordKey = "acc";             // ^acc
        const accordHeadKey = "^^";          // ^^^
        const accordEndKey = "acc-end";      // ^acc-end
        const tabStartKey = "tabs";          // ^tabs
        const tabDivideKey = "tab";          // ^tab
        const tabEndKey = "tabs-end";        // ^tabs-end
        const calloutKey = "callout";        // ^callout
        const calloutEndKey = "callout-end"; // ^callout-end
        const colorKey = "colors";           // ^colors bg:#00000 text:#ffffff link:#dddddd
        const colorBgKey = "bg:";            
        const colorBgTextKey = "text:";
        const colorBgLinkKey = "link:"      
        let pre = prefix.length;

        //pass regular paragraph through as normal
        if (textLower.substring(0,pre) !== prefix) {
            return `<p>${text}</p>\n`;
        } 
        
        //youtube embed
        if (textLower.substring(pre, pre + youTubeKey.length) === youTubeKey) {
            let iframeText = text.substring(pre + youTubeKey.length + 1);
            pos = iframeText.toLowerCase().indexOf("iframe ");
            if (iframeText !== "" && pos > 0) {
                iframeText = '<iframe class="embed-responsive-item rounded" ' 
                                + iframeText.substring(pos + "iframe ".length)
                return '<div class="embed-responsive embed-responsive-16by9 rounded">\n'
                        + iframeText
                        + '</div>\n';
            } else {
                return '<p>missing iframe for YouTube embed</p>';
            }
        } else if (textLower.substring(pre, pre + colorKey.length) === colorKey) {
            let colBgIndex = textLower.indexOf(colorBgKey);
            console.log(`colBgIndex ${colBgIndex}`);
            if (colBgIndex >= 0) {
                colBgIndex += colorBgKey.length;
                let colBg = textLower.substring(colBgIndex, colBgIndex + 7);
                colorThemes[colorFromMarkdownIndex].accentBg = colBg;
                accentBgColor = colBg;
                $selectColorTheme.value = `from markdown`;
            }

            let colTextIndex = textLower.indexOf(colorBgTextKey);
            if (colTextIndex >= 0) {
                colTextIndex += colorBgTextKey.length;
                let colText = textLower.substring(colTextIndex, colTextIndex + 7);
                colorThemes[colorFromMarkdownIndex].accentText = colText;
                accentTextColor = colText;
                $selectColorTheme.value = `from markdown`;
            }
            
            let colLinkIndex = textLower.indexOf(colorBgLinkKey);
            if (colLinkIndex >= 0) {
                colLinkIndex += colorBgLinkKey.length;
                let colLink = textLower.substring(colLinkIndex, colLinkIndex + 7);
                console.log(`colLink ${colLink}`);
                accentLinkColor = colLink;
                $selectColorTheme.value = `from markdown`;
            }
            updateStyle();
            return `<!-- color used -->`;
        //callout end
        } else if (textLower.substring(pre, pre+calloutEndKey.length) == calloutEndKey) {
            return `    </div>\n  </div>\n</div> <!-- end callout -->\n`;
        //callout start
        } else if (textLower.substring(pre, pre+calloutKey.length) == calloutKey) {
            let iconName = textLower.substring(pre + calloutKey.length + 1);
            let suffix = 's';
            if (iconName.indexOf('b-') == 0) {
                iconName = iconName.substring(2);
                suffix = 'b';
            }
            return `<!-- callout begin -->
<div class="card card-graphic">
  <div class="card-body">
    <div class="card-icon">
      <p><span class="fa${suffix} fa-${iconName}"></p>
    </div>
    <div class="card-text">`;
        //tabs end
        } else if (textLower.substring(pre, pre+tabEndKey.length) == tabEndKey) {
            return `        </div>\n      </div>\n    </div>\n  </div>\n</div> <!-- end tabs -->\n`;
        //tab start
        } else if (textLower.substring(pre, pre+tabStartKey.length) == tabStartKey) {
            let completeHtml = `<!-- begin tabs -->\n<div class="tabs-wrapper tabs-horizontal">
  <div class="row">
    <div class="col-12">
        <div class="list-group flex-md-row text-center" role="tablist">`;
            pre += tabStartKey.length;
            let commaIndex = textLower.indexOf(',', pre);
            let activeString = 'active';
            while (commaIndex >= 0) {
                console.log(commaIndex + ", " + textLower);
                let tabTitle = text.substring(pre, commaIndex);
                completeHtml += `<a class="list-group-item list-group-item-action ${activeString}" data-toggle="list" role="tab">
                ${tabTitle}</a>\n`;
                activeString = '';
                pre = commaIndex+1;
                commaIndex = textLower.indexOf(',', pre);
            }
            let tabTitle = text.substring(pre);
            if (tabTitle != null) {
                completeHtml += `<a class="list-group-item list-group-item-action ${activeString}" data-toggle="list" role="tab">
                ${tabTitle}</a>\n`;
            }
            completeHtml += `      </div>
    </div>
    <div class="col-12">
      <div class="tab-content">
        <div class="tab-pane fade show active" role="tabpanel" tabindex="0">`;
            return completeHtml;
            
            
        //tabs divide
        } else if (textLower.substring(pre, pre+tabDivideKey.length) == tabDivideKey) {
            return `</div>
            <div class="tab-pane fade" role="tabpanel" tabindex="0">\n`;
        //accordion end
        } else if (textLower.substring(pre, pre+accordEndKey.length) === accordEndKey) {
        return `      </div>
    </div>
  </div>
</div>\n`;
        //accordion start
        } else if (textLower.substring(pre, pre+accordKey.length) === accordKey) {
                return `<div class="accordion">
                        <div><div><div>\n`;
        //accordion header start (ends previous section)
        } else if (textLower.substring(pre, pre+accordHeadKey.length) === accordHeadKey) {
            return `</div>
                    </div>
                    </div>
                    <div class="card">
                    <div class="card-header">
                      <h2 class="card-title">${text.substring(pre+accordHeadKey.length)}</h2>
                    </div>
                    <div class="collapse">
                      <div class="card-body">\n`;                   
        //banner
        } else if (textLower.substring(pre, pre+bannerKey.length) === bannerKey) {
            return `<h1 class="jumbotron">
                        ${text.substring(pre+bannerKey.length)}
                    </h1>\n`;
        //column marks
        } else if (textLower.substring(pre, pre + colPrefix.length) === colPrefix) {
            pre = pre + colPrefix.length; //include the column prefix in the prefix length
            
            // if this is the column end mark, end the divs
            // (total length is also checked because colEndKey can be empty string)
            if (textLower.length == pre + colEndKey.length 
                && textLower.substring(pre, pre + colEndKey.length) === colEndKey) {
                return '</div>\n</div>\n</div>\n</div>\n';
            }

            let completeHtml = "";
            let colWidth = "-6";
            let colAccent = "";
            let colClass = "";

            if (textLower.substring(pre, pre + col3StartKey.length) === col3StartKey) {
                completeHtml = `<div class="three-col-panels"><div class="row ${rowMargins}">\n`;
                colWidth = "-4";
                pre = pre + col3StartKey.length + 1;
            }
            else if (textLower.substring(pre, pre + col2StartKey.length) === col2StartKey) {
                completeHtml = `<div class="two-col-panels"><div class="row ${rowMargins}">\n`;
                colWidth = "-6";
                pre = pre + col2StartKey.length + 1;

            }
            else if (textLower.substring(pre, pre + colTextStartKey.length) === colTextStartKey) {
                completeHtml = `<div class="row ${rowMargins} justify-content-md-center"><div style="${columnTextStyle}">\n`;
                colWidth = "-12";
                pre = pre + colTextStartKey + 1;
            }
            else if (textLower.substring(pre, pre + col1CenterStartKey.length) === col1CenterStartKey) {
                completeHtml = `<div><div class="row ${rowMargins} justify-content-md-center">\n`;
                colWidth = "-8";
                colClass = "center-column";
                pre = pre + col1CenterStartKey.length + 1;
            }
            else if (textLower.substring(pre, pre + colBreakKey.length) === colBreakKey) {
                completeHtml = '</div>\n</div>\n';
                pre = pre + colBreakKey.length + 1;
            }

            //add accent style if there is an accent key as well
            if (textLower.substring(pre, pre + accentOutlineKey.length) === accentOutlineKey) {
                colAccent = "accentOutline";
                pre = pre + accentOutlineKey.length + 1;
            }
            else if (textLower.substring(pre, pre + accentDarkKey.length) === accentDarkKey) {
                colAccent = "accentDark";
                pre = pre + accentDarkKey.length + 1;
            }
            else if (textLower.substring(pre, pre + accentColorKey.length) === accentColorKey) {
                colAccent = "accentColor";
                pre = pre + accentColorKey.length + 1;

            }
            else if (textLower.substring(pre, pre + accentLiteKey.length) === accentLiteKey) {
                colAccent = "accentLite";
                pre = pre + accentLiteKey.length + 1;
            }

            if (textLower.substring(pre, pre + 1) === '2') colWidth = '-2'; 
            else if (textLower.substring(pre, pre + 1) === '3') colWidth = '-3'; 
            else if (textLower.substring(pre, pre + 1) === '4') colWidth = '-4'; 
            else if (textLower.substring(pre, pre + 1) === '5') colWidth = '-5'; 
            else if (textLower.substring(pre, pre + 1) === '6') colWidth = '-6'; 
            else if (textLower.substring(pre, pre + 1) === '7') colWidth = '-7'; 
            else if (textLower.substring(pre, pre + 1) === '8') colWidth = '-8'; 
            else if (textLower.substring(pre, pre + 1) === '9') colWidth = '-9'; 
            else if (textLower.substring(pre, pre + 2) === '10') colWidth = '-10'; 
            else if (textLower.substring(pre, pre + 2) === '11') colWidth = '-11'; 
            else if (textLower.substring(pre, pre + 2) === '12') colWidth = '-12';
            else if (textLower.substring(pre, pre + 1) === '1') colWidth = '-1';
 
            completeHtml += 
                `<div class="col-md${colWidth} rounded ${colAccent} ${colClass}">\n<div class="rounded px-2 py-1">\n`;
                //
            return completeHtml;
        }
        //section start (sections are within columns)
        else if (textLower.substring(pre, pre + sectionStartKey.length) === sectionStartKey) {
            pre = pre + sectionStartKey.length + 1; //include the column prefix in the prefix length
            let sectionAccent = "";
            //add accent style if there is an accent key as well
            if (textLower.substring(pre, pre + accentOutlineKey.length) === accentOutlineKey) {
                sectionAccent = "accentOutline";
                pre = pre + accentOutlineKey.length + 1;
            }
            else if (textLower.substring(pre, pre + accentDarkKey.length) === accentDarkKey) {
                sectionAccent = "accentDark";
                pre = pre + accentDarkKey.length + 1;
            }
            else if (textLower.substring(pre, pre + accentColorKey.length) === accentColorKey) {
                sectionAccent = "accentColor";
                pre = pre + accentColorKey.length + 1;

            }
            else if (textLower.substring(pre, pre + accentLiteKey.length) === accentLiteKey) {
                sectionAccent = "accentLite";
                pre = pre + accentLiteKey.length + 1;
            }
            return `</div>\n<div class="rounded px-2 py-1 ${sectionAccent}">\n`;
        }
        //section end
        else if (textLower.substring(pre, pre + sectionStartKey.length) === sectionEndKey) {
            //end the last section and starts a new unaccented section
            return `</div>\n<div class="rounded px-2 py-1">`;
        }
    },
/*
    bs(text) {
        return `
<pre style="display: none">
------------------
Meta Data for Page
------------------
${text}
</pre>
`
    }
*/  


};

/*
//tokenizers added to default Tokenizer
const customTokenizer = {
    bs(src) {
        const rule = /^::\s*([\S\s]*)::/;
        const cap = rule.exec(src);
        if (cap) {
            console.log("bs found");
            return {
                type: 'bs',
                raw: cap[0],
                text: cap[1].trim()
            };
        }
    }
};
*/

/*
name: `bs`,
level: `block`,
start(src) { return src.match(/::/)?.index; },
tokenizer(src) {
    const rule = /^::\s*([\S\s]*)::/;
    const match = rule.exec(src);
    if (match) {
        const token = {
            type: `bs`,
            raw: match[0],
            text: match[1].trim(),
            tokens: []
        };
        console.log(match[0]);
        console.log(match[1]);
        console.log(match[1].trim());
        //this.lexer.inline(token.text, token.tokens)
        return token;
    }
},*/

marked.use({ renderer: rendererFile });
//marked.use({ tokenizer: customTokenizer });


//Extensions added to marked.js renderer
//Compare to source: https://github.com/markedjs/marked/blob/master/src/Renderer.js 
//These are for rendering in Brightspace where no style tags are allowed 
const rendererDescription = {
    link(href, title, text) {
        let link = marked.Renderer.prototype.link.call(this, href, title, text);
        
        //[click to download it](google-download:https://googlesharelink)
        link = getGooglizedLinkURL(href, link);
        
        return link.replace(`<a`,`<a target='_blank'`);
    },

    heading(text, level) {
        //color uses accentBgColor if h2 only
        let hColorStyle = level === 2 ? `color: ${accentBgColor}; ` : ``;
        //style with text-align is added
        return `<h${level} style="${hColorStyle}text-align: ${headingAlign} ">${text}</h${level}>\n`;
        //return `<h${level}><b>${text}</b></h${level}>\n`;
    },

    image(href, title, text) {
        let image = marked.Renderer.prototype.image.call(this, href, title, text);
        let roundStyle = `border-radius: 4px; `; //default is slightly rounded corners
        let alignStyle = `margin-right: auto; margin-left: auto; display: block; `; //default is centered block
        let widthStyle = ``;
        let borderStyle = ``;
        if (text != null) {
            if (text.indexOf('^') == 0) {
                let altLengthWithCode = image.length;
                //remove the code, including the ^ from the alt text https://regex101.com/r/zDZBhq/5
                image = image.replace(/alt="\^[\+,\-,o,b]*(w[0-9][0-9])*[\+,\-,o,b]*[ ^]*/i, `alt="`);
                let codeLength = altLengthWithCode - image.length;
                
                let roundIndex = text.indexOf('o');
                let leftIndex = text.indexOf('-');
                let rightIndex = text.indexOf('+');
                let widthIndex = text.indexOf('w');
                let borderIndex = text.indexOf('b');

                //if there is an o in the code, roundClass is rounded-circle
                if (roundIndex >= 1 && roundIndex < codeLength) {
                    roundStyle = `border-radius: 100%;`;
                }

                // - means float left (mr-3 means standard margin on right)
                if (leftIndex >= 1 && leftIndex < codeLength) {               
                    alignStyle = `float: left; margin-right: 1em;`;        
                }
                // + means float right (ml-3 means standard margin on left)
                else if (rightIndex >= 1 && rightIndex < codeLength) {
                    alignStyle = `float: right; margin-left: 1em;`;
                }

                //w and a number between 1 and 9 inclusive means
                // to change the max-width to be 10% to 90%
                if (widthIndex >= 1 && widthIndex < codeLength-2) {
                    let wPercent = +text[widthIndex+1] * 10 + +text[widthIndex+2];      
                    widthStyle = `max-width: ${wPercent}%; height: auto;`;
                }
                
                //b means add a 1 pixel black border
                if (borderIndex >= 1 && borderIndex < codeLength) {
                    borderStyle = `border: 1px solid black; `;
                }
            }    
        }

        //![test google drive image](google:https://drive.google.com/file/d/1CFZgcNtOuE_ETgvTyoez7JgrGz9p_Qql/view?usp=share_link)
        //![test google thumbnail](google-thumb:https://docs.google.com/drawings/d/1PL6Id2pRVH_rWgbAY9QjuqCrsNgYz6j5OYKZ2alLfis/edit?usp=sharing)
        image = getGooglizedImageElement(href, image);

        return image.replace("<img", 
            `<img style="margin-bottom: .5em; ${roundStyle} ${alignStyle} ${widthStyle} ${borderStyle}" `);
    },

    code(code, infostring, escaped) {
        
        let useCopy = infostring.substring(infostring.length-5).toLowerCase() == "^copy";
        let langstring = useCopy ? infostring.substring(0, infostring.length-5) : infostring;
        const lang = (langstring || '').match(/\S*/)[0];
        const originalCode = code;

        //classes that are preset for D2L:
        //d2l-code     : does the highlighting
        //line-numbers : adds line numbers
        //d2l-code-dark: dark mode


        //button tags and script tags are stripped in description mode, so no copy button

        if (!lang) {
            if (useCopy) {
                return `<pre class="d2l-code"><code class="language-none">${originalCode}
</code></pre>`;
            } else {
                return `<pre class="d2l-code"><code class="language-none">${originalCode}
</code></pre>`;
            }
        }
        if (useCopy) {
            return `<pre class="d2l-code"><code class="language-${lang}">${originalCode}
</code></pre>`;
        } else {
            return `<pre class="d2l-code"><code class="language-${lang}">${originalCode}
</code></pre>`;
        }

        //const lang = (infostring || '').match(/\S*/)[0];
        // if (this.options.highlight) {
        //   const out = this.options.highlight(code, lang);
        //   if (out != null && out !== code) {
        //     escaped = true;
        //     code = out;
        //    }
        //  }

        //  /*
        //  const specialBbPre = `<pre style="font-family: 'Lucida Console', Courier, monospace; color: #DDD; background-color: #444; padding: 10px; margin: 10px 0px; max-width: 100%">`;
        //  */
        //  const codePreStyle = `<pre style="font-family: 'Lucida Console', Courier, monospace; font-size: 1em; background-color: ${codeBgColor}; padding: 12px; margin: 0px 0px; max-width: 100%; white-space: pre-wrap;">`;

        //  if (!lang) {
        //    return codePreStyle + '<code>'
        //     + code
        //     + '</code></pre>\n';
        // }
    
        // return codePreStyle + '<code class="'
        //   + this.options.langPrefix
        //   + lang
        //   + '">'
        //   + code
        //   + '</code></pre>\n';
          
    },

    codespan(text) { 
        return `<code style="background-color: ${codeBgColor}; color: #000; padding: 0 .25em 0 .25em; border-radius: .25em; ">${text}</code>`;
    },


    //em emphasis/italic is being used for custom spans like icons and keyboard
    em(text) {
        const iconKey = '^icon';
        const keyKey = '^key';
        let i = text.indexOf(iconKey);
        if (i < 0) {
            i = text.indexOf(keyKey);
            if (i < 0) {
                //basic italic
                return `<em>${text}</em>`;
            }
            //key
            else {
                let keyName = text.substring(i+keyKey.length+1);
                if (keyName) {
                    return `<kbd>${keyName}</kbd>`;
                }
                else {
                    return `<span style="color: red">key not named</span>`;
                }
            }
        } else {
            //icon
            let iconName = text.substring(i+iconKey.length+1);
            if (iconName) {
                let suffix = 's';
                if (iconName.indexOf('b-') == 0) {
                    iconName = iconName.substring(2);
                    suffix = 'b';
                }
                return `<i class="fa${suffix} fa-${iconName}"></i>`;
            }
            else {
                return `<span style="color: red">icon not named</span>`;
            } 
        } 
    },

    paragraph(text) {
        //we are hijacking the paragraph renderer here
        //check for specific starting tokens and do something with them
        //this could be extended with new tokenizers I believe, but for now this works

        //Responsive YouTube iframe
        //Usage: Start paragraph with ^youtube and paste embed <iframe> code from youtube one space after it.

        textLower = text.toLowerCase();
        const prefix = "^";
        /* columns          : responsive divs horizontally aligned as columns
            ^= = =           (deprectated) start 3 column div and begin first column
            ^= =             start multi-column row div and begin first column
            ^=^              start row with 1 centered column at reading width
            ^===             column break (begin next column)
            ^=               end column div (return to normal single column)
            ^=t=             start multi-column text area
            
            accents          : add at end of column start key or break ^=== ,,
            ,,,,             accent level Outline - outlined with accent color
            ,,,              accent level Dark - strong (invert)
            ,,               accent level Color - colorful
            ,                accent level Lite - subtle separation
        */
        const colPrefix = "=";
        const colTextStartKey = "t=";        // ^=t=
        const col2StartKey = " =";           // ^= =
        const col3StartKey = " = =";         // ^= = = deprecated
        const col1CenterStartKey = "^";      // ^=^
        const colBreakKey = "==";            // ^===
        const colEndKey = "";                // ^=
        const accentOutlineKey = ",,,,";
        const accentDarkKey = ",,,";
        const accentColorKey = ",,";
        const accentLiteKey = ",";
        const youTubeKey = "youtube";        // ^youtube
        const bannerKey  = "^";              // ^^
        const accordKey = "acc";             // ^acc
        const accordHeadKey = "^^";          // ^^^
        const accordEndKey = "acc-end";      // ^acc-end
        const tabStartKey = "tabs";          // ^tabs
        const tabDivideKey = "tab";          // ^tab
        const tabEndKey = "tabs-end";        // ^tabs-end
        const calloutKey = "callout";        // ^callout
        const calloutEndKey = "callout-end"; // ^callout-end
        let pre = prefix.length;

        //pass regular paragraph through as normal
        if (textLower.substring(0,pre) !== prefix) {
            return `<p>${text}</p>\n`;
        } 
        
        //youtube embed
        if (textLower.substring(pre, pre + youTubeKey.length) === youTubeKey) {
            let iframeText = text.substring(pre + youTubeKey.length + 1);
            pos = iframeText.toLowerCase().indexOf("iframe ");
            if (iframeText !== "" && pos > 0) {
                iframeText = '<iframe class="embed-responsive-item rounded" ' 
                                + iframeText.substring(pos + "iframe ".length)
                return '<div class="embed-responsive embed-responsive-16by9 rounded">\n'
                        + iframeText
                        + '</div>\n';
            } else {
                return '<p>missing iframe for YouTube embed</p>';
            }
        //callout end
        } else if (textLower.substring(pre, pre+calloutEndKey.length) == calloutEndKey) {
            return `    </div>\n  </div>\n</div> <!-- end callout -->\n`;
        //callout start
        } else if (textLower.substring(pre, pre+calloutKey.length) == calloutKey) {
            let iconName = textLower.substring(pre + calloutKey.length + 1);
            let suffix = 's';
            if (iconName.indexOf('b-') == 0) {
                iconName = iconName.substring(2);
                suffix = 'b';
            }
            return `<!-- callout begin -->
<div class="card card-graphic">
  <div class="card-body">
    <div class="card-icon">
      <p><span class="fa${suffix} fa-${iconName}"></p>
    </div>
    <div class="card-text">`;
        //tabs end
        } else if (textLower.substring(pre, pre+tabEndKey.length) == tabEndKey) {
            return `        </div>\n      </div>\n    </div>\n  </div>\n</div> <!-- end tabs -->\n`;
        //tab start
        } else if (textLower.substring(pre, pre+tabStartKey.length) == tabStartKey) {
            let completeHtml = `<!-- begin tabs -->\n<div class="tabs-wrapper tabs-horizontal">
  <div class="row">
    <div class="col-12">
        <div class="list-group flex-md-row text-center" role="tablist">`;
            pre += tabStartKey.length;
            let commaIndex = textLower.indexOf(',', pre);
            let activeString = 'active';
            while (commaIndex >= 0) {
                console.log(commaIndex + ", " + textLower);
                let tabTitle = text.substring(pre, commaIndex);
                completeHtml += `<a class="list-group-item list-group-item-action ${activeString}" data-toggle="list" role="tab">
                ${tabTitle}</a>\n`;
                activeString = '';
                pre = commaIndex+1;
                commaIndex = textLower.indexOf(',', pre);
            }
            let tabTitle = text.substring(pre);
            if (tabTitle != null) {
                completeHtml += `<a class="list-group-item list-group-item-action ${activeString}" data-toggle="list" role="tab">
                ${tabTitle}</a>\n`;
            }
            completeHtml += `      </div>
    </div>
    <div class="col-12">
      <div class="tab-content">
        <div class="tab-pane fade show active" role="tabpanel" tabindex="0">`;
            return completeHtml;
            
            
        //tabs divide
        } else if (textLower.substring(pre, pre+tabDivideKey.length) == tabDivideKey) {
            return `</div>
            <div class="tab-pane fade" role="tabpanel" tabindex="0">\n`;
        //accordion end
        } else if (textLower.substring(pre, pre+accordEndKey.length) === accordEndKey) {
        return `      </div>
    </div>
  </div>
</div>\n`;
        //accordion start
        } else if (textLower.substring(pre, pre+accordKey.length) === accordKey) {
                return `<div class="accordion">
                        <div><div><div>\n`;
        //accordion header start (ends previous section)
        } else if (textLower.substring(pre, pre+accordHeadKey.length) === accordHeadKey) {
            return `</div>
                    </div>
                    </div>
                    <div class="card">
                    <div class="card-header">
                      <h2 class="card-title">${text.substring(pre+accordHeadKey.length)}</h2>
                    </div>
                    <div class="collapse">
                      <div class="card-body">\n`;                   
        //banner
        } else if (textLower.substring(pre, pre+bannerKey.length) === bannerKey) {
            const bannerStyle = `background-color: ${accentBgColor}; color: ${accentTextColor}; text-align:${headingAlign}; padding: 1.875rem; border-radius: .25rem;`
            return `<h1 style="${bannerStyle}">
    ${text.substring(pre+bannerKey.length)}
</h1>\n`;
        //column marks
/*
//goal html for multi-column using inline styles
<div style="display: flex; flex-flow: row wrap;">
  <div style="width: 390px; background-color: pink; padding: 0 .5em; border-radius: 10px;">
    <p><strong>Follow the instructions</strong> <strong>on this linked page</strong>. Return here, when complete.</p>
  </div>
  <div style="width: 390px; background-color: lightyellow; padding: 0 .5em;">
    <p>Upload these items, then write in the comment field and hit submit.</p>
    <ol>
      <li>Zipped project file</li>
      <li>Screenshot of completed work.</li>
    </ol>
  </div>
</div>
*/

        } else if (textLower.substring(pre, pre + colPrefix.length) === colPrefix) {
            pre = pre + colPrefix.length; //include the column prefix in the prefix length
            
            // if this is the column end mark, end the divs
            // (total length is also checked because colEndKey can be empty string)
            if (textLower.length == pre + colEndKey.length 
                && textLower.substring(pre, pre + colEndKey.length) === colEndKey) {
                return '</div>\n</div>\n';
            }

            let completeHtml = "";
            let totalDescriptionWidth = 780;
            let segmentWidth = totalDescriptionWidth / 12;
            let colWidth = 6 * segmentWidth;
            let colAccent = "";
            let colAccentStyles = "";
            let colBorderStyles = "";
            let colClass = "";

            if (textLower.substring(pre, pre + col3StartKey.length) === col3StartKey) {
                completeHtml = `<div style="display: flex; flex-flow: row wrap;">\n`;
                //completeHtml = `<div class="three-col-panels"><div class="row ${rowMargins}">\n`;
                colWidth = 4 * segmentWidth;
                pre = pre + col3StartKey.length + 1;
            }
            else if (textLower.substring(pre, pre + col2StartKey.length) === col2StartKey) {
                completeHtml = `<div style="display: flex; flex-flow: row wrap;">\n`;
                //completeHtml = `<div class="two-col-panels"><div class="row ${rowMargins}">\n`;
                colWidth = 6 * segmentWidth;
                pre = pre + col2StartKey.length + 1;

            }
            else if (textLower.substring(pre, pre + colTextStartKey.length) === colTextStartKey) {
                completeHtml = `<div style="display: flex; flex-flow: row wrap; justify-content: center">\n`;
                colWidth = 8 * segmentWidth;
                //completeHtml = `<div class="row ${rowMargins} justify-content-md-center"><div style="${columnTextStyle}">\n`;
                //colWidth = 12 * segmentWidth;
                pre = pre + colTextStartKey + 1;
            }
            else if (textLower.substring(pre, pre + col1CenterStartKey.length) === col1CenterStartKey) {
                completeHtml = `<div style="display: flex; flex-flow: row wrap; justify-content: center">\n`;
                //completeHtml = `<div><div class="row ${rowMargins} justify-content-md-center">\n`;
                colWidth = 8 * segmentWidth;
                //colClass = "center-column";
                pre = pre + col1CenterStartKey.length + 1;
            }
            else if (textLower.substring(pre, pre + colBreakKey.length) === colBreakKey) {
                completeHtml = '</div>\n';
                pre = pre + colBreakKey.length + 1;
            }

            //add accent style if there is an accent key as well
            if (textLower.substring(pre, pre + accentOutlineKey.length) === accentOutlineKey) {
                colAccent = "accentOutline";
                colBorderStyles = `border-width: 3px; border-style: solid; border-radius: .25em; border-color: ${accentBgColor};`;
                pre = pre + accentOutlineKey.length + 1;
            }
            else if (textLower.substring(pre, pre + accentDarkKey.length) === accentDarkKey) {
                colAccent = "accentDark";
                colAccentStyles = `background-color: ${accentDarkBgColor}; color: ${accentDarkTextColor};`;
                colBorderStyles = `border-radius: .25em;`;
                pre = pre + accentDarkKey.length + 1;
            }
            else if (textLower.substring(pre, pre + accentColorKey.length) === accentColorKey) {
                colAccent = "accentColor";
                colAccentStyles = `background-color: ${accentBgColor}; color: ${accentTextColor};`;
                colBorderStyles = `border-radius: .25em;`;
                pre = pre + accentColorKey.length + 1;

            }
            else if (textLower.substring(pre, pre + accentLiteKey.length) === accentLiteKey) {
                colAccent = "accentLite";
                colAccentStyles = `background-color: ${accentLiteBgColor};`;
                colBorderStyles = `border-radius: .25em;`;
                pre = pre + accentLiteKey.length + 1;
            } 

            if (textLower.substring(pre, pre + 1) === '2') colWidth = 2 * segmentWidth; 
            else if (textLower.substring(pre, pre + 1) === '3') colWidth = 3 * segmentWidth; 
            else if (textLower.substring(pre, pre + 1) === '4') colWidth = 4 * segmentWidth; 
            else if (textLower.substring(pre, pre + 1) === '5') colWidth = 5 * segmentWidth; 
            else if (textLower.substring(pre, pre + 1) === '6') colWidth = 6 * segmentWidth; 
            else if (textLower.substring(pre, pre + 1) === '7') colWidth = 7 * segmentWidth; 
            else if (textLower.substring(pre, pre + 1) === '8') colWidth = 8 * segmentWidth; 
            else if (textLower.substring(pre, pre + 1) === '9') colWidth = 9 * segmentWidth; 
            else if (textLower.substring(pre, pre + 2) === '10') colWidth = 10 * segmentWidth; 
            else if (textLower.substring(pre, pre + 2) === '11') colWidth = 11 * segmentWidth; 
            else if (textLower.substring(pre, pre + 2) === '12') colWidth = 12 * segmentWidth;
            else if (textLower.substring(pre, pre + 1) === '1') colWidth = 1 * segmentWidth;

            if (colBorderStyles.indexOf("border-width") >= 0) colWidth -= 6;

            completeHtml += 
                `<div style="width: ${colWidth}px; ${colAccentStyles} ${colBorderStyles} padding: 0 .5em; margin: 0 0 .5em 0;">\n`;
                //`<div class="col-md${colWidth} rounded pt-0 ${colAccent} ${colClass}">\n`;
                //
            return completeHtml;
        }
    }
};

updateStyle();
convert();

//returns image element text that converted google share urls to image urls using md like this:
//![test google drive image](google:https://drive.google.com/file/d/1CFZgcNtOuE_ETgvTyoez7JgrGz9p_Qql/view?usp=share_link)
//![test google thumbnail](google-thumb:https://docs.google.com/drawings/d/1PL6Id2pRVH_rWgbAY9QjuqCrsNgYz6j5OYKZ2alLfis/edit?usp=sharing)
function getGooglizedImageElement(href, imageElementText) {
    if (href == null) return imageElementText;
    let googleThumb = `google-thumb:`;
    let googleImage = `google:`;
    let thumbIndex = href.toLowerCase().indexOf(googleThumb);
    let imageIndex = href.toLowerCase().indexOf(googleImage);
    let googID = null;
    if (thumbIndex == 0 || imageIndex == 0) {
        googID = href.match(/[-\w]{25,}(?!.*[-\w]{25,})/); //https://regex101.com/r/3EuU53/1 from https://stackoverflow.com/questions/16840038/easiest-way-to-get-file-id-from-url-on-google-apps-script
        if (googID != null) {
            if (thumbIndex == 0) {
                imageElementText = imageElementText.replace(/src="[^"]*"/i, `src="https://lh3.googleusercontent.com/d/${googID}=w320"`);
            }
            if (imageIndex == 0) {
                imageElementText = imageElementText.replace(/src="[^"]*"/i, `src="https://drive.google.com/uc?id=${googID}"`);
            }
        }
        else {
            if (thumbIndex == 0) {
                imageElementText = imageElementText.replace(/src="[^"]*"/i, `src="${href.substring(0, thumbIndex) + href.substring(thumbIndex+googleThumb.length)}"`);
            }
            if (imageIndex == 0) {
                imageElementText = imageElementText.replace(/src="[^"]*"/i, `src="${href.substring(0, imageIndex) + href.substring(imageIndex+googleImage.length)}"`);
            }
        }
    }
    return imageElementText;
}

//returns link element text that converted google share url to direct download links using md like this:
//[click to download it](google-download:https://googlesharelink)
function getGooglizedLinkURL(href, linkElementText) {
    if (href == null) return linkElementText;
    let googleDownload = `google-download:`;
    let dlIndex = href.toLowerCase().indexOf(googleDownload);
    let googID = ``;
    if (dlIndex == 0) {
        googID = href.match(/[-\w]{25,}(?!.*[-\w]{25,})/); //https://regex101.com/r/3EuU53/1 from https://stackoverflow.com/questions/16840038/easiest-way-to-get-file-id-from-url-on-google-apps-script
        if (googID != null)
            linkElementText = linkElementText.replace(/href="[^"]*"/i, `href="https://drive.google.com/uc?id=${googID}&export=download"`);
        else 
            linkElementText = linkElementText.replace(/href="[^"]*"/i, `href="${href.substring(dlIndex+googleDownload.length)}"`);
    }
    return linkElementText;
}


function changeResultType() {
    switch($selectResultType.value) {
    case `standalone`: 
        marked.setOptions( {
            renderer: new marked.Renderer(),
            //tokenizer: new marked.Tokenizer(),
            highlight: (code, lang) => {
                if (Prism.languages[lang]) {
                    return Prism.highlight(code, Prism.languages[lang], lang);
                } else {
                    return code;
                }
            },
            pedantic: false,
            gfm: true,
            breaks: false,
            sanitize: false,
            smartLists: true,
            smartypants: false,
            xhtml: false
        });
        marked.use({ renderer: rendererFile });
        //marked.use({ tokenizer: customTokenizer });

        convert();
        break;
    case `file`:
        marked.setOptions( {
            renderer: new marked.Renderer(),
            //tokenizer: new marked.Tokenizer(),
            highlight: (code, lang) => {
                if (Prism.languages[lang]) {
                    return Prism.highlight(code, Prism.languages[lang], lang);
                } else {
                    return code;
                }
            },
            pedantic: false,
            gfm: true,
            breaks: false,
            sanitize: false,
            smartLists: true,
            smartypants: false,
            xhtml: false
        });
        marked.use({ renderer: rendererFile });
        //marked.use({ tokenizer: customTokenizer });

        convert();
        break;
    case `description`:
        marked.setOptions( {
            renderer: new marked.Renderer(),
            //tokenizer: new marked.Tokenizer(),
            highlight: (code, lang) => {
                //highlight.js not prism
                const validLanguage = hljs.getLanguage(lang) ? lang : 'plaintext';
                return hljs.highlight(validLanguage, code).value;
            },
            pedantic: false,
            gfm: true,
            breaks: false,
            sanitize: false,
            smartLists: true,
            smartypants: false,
            xhtml: false
        });
        marked.use({ renderer: rendererDescription });
        //marked.use({ tokenizer: customTokenizer });

        convert();
        break;
    default:
        console.debug("changeResultType has a bad select value");
    }
}

//document.getElementById('inputfile')
//            .addEventListener('change', function() {
function loadFile(){             
    var fr=new FileReader();
    fr.onload=function(){
        $markdownTextArea.value = fr.result;
        handleNewText();
    }
    fr.readAsText(this.files[0]);
}

function toggleHeadings() {
    if (headingAlign == 'center') {
        $btnHeadings.innerHTML = `<i class="fas fa-align-left"></i>`;
        headingAlign = 'left';
    } else {
        $btnHeadings.innerHTML = `<i class="fas fa-align-center"></i>`;
        headingAlign = 'center';
    }
    updateStyle();
    convert();
}

function toggleStandalone() {
    convert();
}

function updateStyle() {
    
    let styleAccents = `.accentOutline {
    border-width: 3px; 
    border-style: solid;
    border-radius: .25em;
    border-color: ${accentBgColor};
}
.accentColor {
    background-color: ${accentBgColor};
    color: ${accentTextColor};
}
.accentColor h1, .accentColor h2, .accentColor h3, .accentColor h4, .accentColor h5, .accentColor h6 {
    color: ${accentTextColor};
} 
.accentColor a  {
    color: ${accentLinkColor};
    border-color: ${accentLinkColor};
    font-weight: bold;
    border-bottom-width: 2px;
}
.jumbotron {
    background-color: ${accentBgColor};
    color: ${accentTextColor};
}
.jumbotron a {
    color: ${accentTextColor};
    border-color: ${accentLinkColor};
}

.accentDark {
    background-color: ${accentDarkBgColor};
    color: ${accentDarkTextColor};
}
.accentDark h1, .accentDark h2, .accentDark h3, .accentDark h4, .accentDark h5, .accentDark h6 {
    color: ${accentDarkTextColor};
} 
.accentDark a {
    color: ${accentDarkLinkColor};
    border-color: ${accentDarkLinkColor};
}
.accentLite {
    background-color: ${accentLiteBgColor};
    color: black;
}
.accentLite h1, .accentLite .h1, .accentLite h3, .accentLite .h3, .accentLite h4, .accentLite .h4, .accentLite h5, .accentLite .h5, .accentLite h6, .accentLite .h6 {
    color: black;
} 
.accentLite h2, .accentLite .h2 {
    color: ${accentBgColor};
}

.card-graphic {
    border-color: ${accentBgColor};
}

.card-graphic .card-body .card-icon .fa,
.card-graphic .card-body .card-icon .fas, 
.card-graphic .card-body .card-icon .fab {
    font-size: 3.125rem;
    min-width: 5.313rem;
    text-align: center;
    color: ${accentBgColor};
}

ol.large-number li:before {
    color: ${accentBgColor};
}

ol.box-number li:before,
ol.box-alpha li:before {
    background-color: ${accentBgColor};
    color: ${accentTextColor};
}
`;



    let styleHeadings = `.h1, .h2, .h3, .h4, .h5, .h6,
h1, h2, h3, h4, h5, h6 {
    text-align: ${headingAlign};
}
.h2, h2 {
    color: ${accentBgColor};
}
.accentColor .h2, .accentColor h2 {
    color: ${accentTextColor};
}
.accentDark .h2, .accentDark h2 {
    color: ${accentDarkTextColor};
}
.two-col-panels .card .card-body h2 {
    color: ${accentBgColor};
    margin-top: 0px;
}
`;

    let styleCode = `code {
    background-color: ${codeBgColor};
    color: #000;
    border-radius: .25em;
    padding: 0 .25em 0 .25em;
}`;

    let styleCenterColumn = `/*
    .center-column {
    border-width: 3px; 
    border-style: solid;
    border-radius: .25em;
    border-color: ${accentBgColor};
    margin-bottom: 1em;
    margin-top: 1em;
}
*/`;

    let styleTables = `.table-responsive td,
.table-responsive tr,
.table-responsive caption {
    background-color: #fff;
}`;

    let styleColumns = `.col, .col-1, .col-10, .col-11, .col-12, 
.col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, 
.col-auto, .col-lg, .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, 
.col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, 
.col-lg-8, .col-lg-9, .col-lg-auto, .col-md, .col-md-1, .col-md-10, 
.col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, 
.col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-auto, .col-sm, 
.col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, 
.col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, 
.col-sm-auto, .col-xl, .col-xl-1, .col-xl-10, .col-xl-11, .col-xl-12, 
.col-xl-2, .col-xl-3, .col-xl-4, .col-xl-5, .col-xl-6, .col-xl-7, 
.col-xl-8, .col-xl-9, .col-xl-auto {
    padding-left: 1px;
    padding-right: 1px;
}`;

    tagsStyle = `\n<style>
  ${styleCode}
  ${styleHeadings}
  ${styleAccents}
  ${styleCenterColumn}
  ${styleTables}
  ${styleColumns}
</style>`;
}

function clearOriginal() {
    $markdownTextArea.value = "";
    convert();
    console.log("clearOriginal called");
}

function restore() {
    $markdownTextArea.value = markdownMemory;
    handleNewText();
    console.log("restore called");
}

function copyMd() {
    //Get the text field
    var copyText = $markdownTextArea;
    
    //Select the text
    copyText.select();
    //copyText.setSelectionRange(0, 99999); /*For mobile devices*/
    
    /* Copy the text inside the text field */
    if (!navigator.clipboard) {
        document.execCommand("copy");
    } else {
        navigator.clipboard.writeText(copyText.value).then(
            function() {
                console.log("markdown copied to clipboard");
            })
        .catch (
            function() {
                console.log("unable to copy to clipboard");
            }
        )
    }
}

function copyHtml() {
    //Get the text field
    var copyText = $htmlTextArea;
    
    //Select the text
    copyText.select();
    //copyText.setSelectionRange(0, 99999); /*For mobile devices*/
    
    /* Copy the text inside the text field */

    if (!navigator.clipboard) {
        document.execCommand("copy");
    } else {
        navigator.clipboard.writeText(copyText.value).then(
            function() {
                console.log("html copied to clipboard");
            })
        .catch (
            function() {
                console.log("unable to copy to clipboard");
            }
        )
    }
}

let timeout = setTimeout(handleNewText, 100);
function handleNewText() {
    clearTimeout(timeout);
    
    //remember this for restore
    markdownMemory = $markdownTextArea.value;
    
    $selectExample.value = "None";
    timeout = setTimeout(convert, 900);
}

function processMetaSection(text) {    
    while(true) {
        let nextType = text.match(/\n?([^:]*):\n/); // group 1 has just type without colon [1]: https://regex101.com/r/Z398Wa/1
        let nextContent = text.match(/:\n([^:]*)\n.*:\n/); // group 1 has content [1]: https://regex101.com/r/u3XDoL/1
        if (nextType && nextContent) {
            metaItems.push( { type: nextType[1], content: nextContent[1] } );
            text = text.substring(nextType[0].length + nextContent[1].length);
            console.log(text);
        }
        else break;
    }

    addMetaCopyTypes(); //add the types as options on the page's select input

    console.log(metaItems);
}

function convert() {
    let original = $markdownTextArea.value;

    //if this is the converted html and not the markdown, look for the md embedded within
    //and replace it with the original markdown
    let embedString = `^source markdown start^`;
    let embedCodeStartIndex = original.indexOf(embedString);
    if (embedCodeStartIndex > -1) {
        let embedStartIndex = embedCodeStartIndex + embedString.length + 1;
        let embeddedMarkdown = original.substring(embedStartIndex, original.indexOf(`^source markdown end^`));
        embeddedMarkdown = embeddedMarkdown.replaceAll('&lt;', '<');
        original = embeddedMarkdown;
        $markdownTextArea.value = original;
    }

    //separate the top meta section and the markdown for the page 
    //uses three colons ::: to show division between meta area and markdown
    metaItems.length = 0; //clear anything in the metaItems from last convert
    const rule = /[\S\s]*:::\n/;
    let meta = original.match(rule);
    let pageMarkdown = original;
    if (meta != null) {
        pageMarkdown = original.substring(meta[0].length);
        console.log(`markdown section: ${pageMarkdown}`);
        processMetaSection(meta[0]);
    }

    //single-threaded (no worker)

    //convert to html from markdown using marked()
    let convertedHTML = marked(pageMarkdown);

    let embeddedMarkdown = `
<!-------------------------------------------------------------->    
<pre style="display: none">
--------------------------------------------------
Source markdown in custom md-to-brightspace format
--------------------------------------------------
^version ${versionCode}^
^source markdown start^
${original.replaceAll('<', '&lt;')}
^source markdown end^
</pre>
<!-------------------------------------------------------------->    
`;

    //display the page preview and show the html created
    switch($selectResultType.value) {
    case `standalone`:
        $previewTextArea.innerHTML = tagsStyle + convertedHTML;
        $htmlTextArea.value = tagsTopStandalone + tagsStyle + tagsTopClosing + convertedHTML + embeddedMarkdown + tagsBottomStandalone;
        break;
    case `file`:
        $previewTextArea.innerHTML = tagsStyle + convertedHTML;
        $htmlTextArea.value = tagsTop + tagsStyle + tagsTopClosing + convertedHTML + embeddedMarkdown + tagsBottom;
        break;
    case `description`:
        $previewTextArea.innerHTML = convertedHTML;
        $htmlTextArea.value = convertedHTML + embeddedMarkdown;
        break;
    default:
        console.debug(`convert: selectResultType value not matching a case`);
    }
    
}

//too slow
/*
function stripEmbeds( html ) {
    let emptyIFrame = `<iframe class="embed-responsive-item rounded" src=""></iframe>`;
    let endLength = '</iframe>'.length -1;
    
    let start = html.indexOf('<iframe', 0);
    let strippedHtml = "";
    if (start > -1) {
        strippedHtml = html.substring(0, start);
    } else {
        return html;
    }
    let end = html.indexOf('</iframe>', start);
    while (start > -1 && end > -1) {
        strippedHtml += html.substring(0, start) + emptyIFrame + html.substring(end + endLength, html.length);
        start = html.indexOf('<iframe', end + endLength);
        if (start > -1) end = html.indexOf('</iframe>', start);
    }
    return strippedHtml;
}
*/

let examples = [
    { 
        title: 'None',
        md: ``
    },
    {
        title: 'Basic Markdown',
        md: `^=^

A Markdown Primer *^icon b-markdown* 
==================================

Markdown is Text with Benefits
------------------------------

The beauty of Markdown is that it can stay in a **simple text file** that you can keep, copy, manipulate, open, and read with any text editor, yet it facilitates creating formatted pages. 

It is most commonly used as a more _human-readable_ format than HTML that is used to create web pages. (HTML uses tags with distracting ornamentations making it harder to read the content while editing.)

^=

[NOTE: You will see some symbols (like the ^= above)in this document that are not part of the original Markdown specification. They are powerful custom options explored in other example pages.]: #

^= =

^callout arrow-left

Be sure to examine the **original markdown** on the left to learn how this page is created with simple text. 

^callout-end

^===

Digging Deeper
--------------

This page will cover basics, quickly. [Try this more thorough guide.](https://www.markdownguide.org/basic-syntax/)

To get feeling for the origin and intent, visit [this summary of the basics][gruber] by Markdown's creator, John Gruber.

[gruber]:https://daringfireball.net/projects/markdown/basics

^=

^=^

Headings
--------

Headings can be created by adding an "underline" of equal signs \`===\` for the most prominent heading (level 1) or dashes \`---\` for a level 2 heading.


You can also use a hashtag and a space \`# \` before a heading. The number of hashtags determines the heading level.

^=

^= =

markdown text:

\`\`\`
Heading level 1
===============

Heading level 2
---------------

# Heading level 1

## Heading level 2

### Heading level 3

#### Heading level 4

##### Heading level 5

###### Heading level 6
\`\`\`

^===

resulting format:

Heading level 1
===============
Heading level 2
---------------

# Heading level 1

## Heading level 2

### Heading level 3

#### Heading level 4

##### Heading level 5

###### Heading level 6

^=

^=^

Paragraphs
----------

Start a new paragraph by entering a blank line before the next paragraph. Just pressing enter/return once won't start a new paragraph. You need to type it twice to get that blank line.

To start a new line (line break) _without a blank line_, type two spaces before pressing enter. It's invisible, unfortunately, but it does work! (The example below shows underscores in place of where you would need to type spaces.)

^=

^= =

\`\`\`
My first paragraph.

My second paragraph.

My poem has more.__
Not a single lonely line.__  
In fact, it has three.

Oops.
This ends up...
on the same line!
No blank lines or endings with two spaces.
\`\`\`

^===

My first paragraph.

My second paragraph.

My poem has more.  
Not a single lonely line.  
In fact, it has three.

Oops.
This ends up...
on the same line!
No blank lines or endings with two spaces.

^=

^callout exclamation-triangle

TO-DO: still need sections on links, images, and lists

^callout-end

^callout b-github

You may want to keep your text files in a place where they can be shared across computers and version controlled (keeping a record of all changes so you can always go back to old versions). Using a combination of [Github][] and [Github Desktop][] is a free and relatively simple way to do this. 

[github]:https://github.com/
[github desktop]:https://desktop.github.com/

If you are not worried about keeping older versions, you can go much simpler by using *^icon cloud* OneDrive (as part of Office 365), or other cloud drive solutions like *^icon b-google-drive* Google Drive, *^icon b-apple* iCloud Drive, or *^icon b-dropbox* Drop Box.

^callout-end  
`
    },
    {
        title: 'Callouts & Icons',
        md: `# Callout & Icon Examples

^= =

^callout book

There is a bunch of stuff to say about this, but you should read it in our text book, pages 110-520.

^callout-end

^===

### markdown

\`\`\`
^callout book

There is a bunch of stuff to say about this, but you should read it in our text book, pages 110-520.

^callout-end
\`\`\`

^=

^=^ ,

You can find the options for icon graphics at [this Font Awesome icons page][fa] (version 5 and only including the free options)

[fa]:https://fontawesome.com/v5/search?m=free

Here are some options:

|   |   |   |   |
|:-:|---|:-:|---|
|*^icon users*|users |*^icon user-secret*  | user-secret |
|*^icon info-circle*|info-circle|*^icon question-circle*|question-circle
|*^icon b-discord*|b-discord|*^icon b-google-drive*|b-google-drive ^cap Notice the necessary b- added in front of the business icon names.^ 

^=

^callout comment

You can add these icons into the middle of paragraphs, too. Start by beginning an italic markdown \`*\` or \`_\`, add the code \`^icon\` and a space, then the name of the icon you want, and a closing italic mark \`*\` or \`_\`.

For instance, \`*^icon book-open*\` will give you: *^icon book-open*.

^callout-end

^=^

*^icon keyboard* For writing keyboard shortcuts you can use a similar method to the inline icons. Just use \`*^key\` and the name of the key before the closing \`*\`. 

\`*^key ctrl*\` and \`*^key S*\` for the keys below.

Press *^key ctrl*-*^key S* to Save your work!

^=`
    },
    { 
        title: 'List Styles',
        md: `## Ordered List Style Options

^=^

Just add a style choice anywhere within the numbered list (not before or after) For instance:

\`1. The 1st list item\`**\`^large^\`**

^=

---

**^large^**

1. Stuff ^large^
2. More stuff
    1. sub stuff
    2. more sub stuff
3. More stuff
5. The end of the list
---

**^box^**

1. Stuff ^box^
2. More stuff
    1. sub stuff
    2. more sub stuff
3. More stuff
5. The end of the list
----

**^box-alpha^**

1. Stuff ^box-alpha^
2. More stuff
    1. sub stuff
    2. more sub stuff
3. More stuff
5. The end of the list
---

1. Stuff
2. More stuff
    1. sub stuff
    2. more sub stuff
3. More stuff
5. The end of the list
`
    },
    {
        title: 'Multiple Columns',
        md: `# Multiple Column Styles

This is a paragraph at the normal size and it goes quite wide and is not the most readable or scannable (long way from left to right to hold attention)

Using \`^= =\` begins a multi-column section up until \`^=\` is used to end that section. Both need a blank line above and below them.

^= =

So, you can have a section on the left that is easier to read without stretching so far...

^=== ,

...and a section beside it that can have additional related information like a video, a picture, etc.

^=== ,

There is a \`^===\` used to show where to divide the columns within the section (each takes up half a page by default and will drop down to next row automatically)

^===

The columns are responsive and the right hand row will move below the left when the page is shrunken down.

^=

^= = ,

Columns can have different formatting using a series of commas. This one has just one comma after the row starter:

\`^= = ,\`

Note that there is a space before the comma.

^=== ,,,,

This column has four commas after the column divider, like this: \`^=== ,,,,\`

There are currently 4 styles and you can nest rows/cols:

^= = ,,

2 commas

\`^= = ,,\`

^=== ,,,

3 commas

\`^= = ,,,\`

^=

^=

#

^= = ,, 8

The columns can be individually sized, always adding up to 12 potential columns

This row's first column was set to be size 8 and have style 2 applied

\`^= = ,, 8\`

Note the space added before and after the series of commas

^=== 4

This is a size 4 column with no style added.

\`^=== 4\`

To add a small blank line like the one above this row, use an empty heading \`#\`

^=

^=^

This is the easy reading centered section, which is just a single column set up to be in the middle of the page.

Start with \`^=^\` and end with \`^=\`

^=

^=^ ,,,, 10

You can adjust the size and style like the others, but it will always be centered and not made for use with dividers.

This one starts with \`^=^ ,,,, 10\`

^=

### Three or More Columns

^= = 4

You can create more than 2 columns by using sizes that are smaller

^=== , 4

For instance, this is a row with three size-4 columns

^=== 4

Columns need to add up to 12 in one row, or it will leave some blank columns on the right.

^= 
`
    },
    {
        title: 'Basics',
        md: `^^ MD to Brightspace Web Page Example

# Heading 1

^=^

Columns work almost the same way as with md2Bb. The following problem is now fixed: There is one key difference you may have to edit old md files for however: **You need a starting \`^= =\` or \`^= = =\` for each "row" now ending with a \`^=\`, and separating with a \`^===\`. If you add additional panels with a divider beyond the column number it still piles in extra columns.

This centered column is made using \`^=^\` with no dividers and closed with \`^=\`

^=

## Heading 2

^= = , 7

^YouTube <iframe width="560" height="315" src="https://www.youtube.com/embed/vFjXKOXdgGo" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

This is a good YouTube video about learning to make games using a game engine and how to approach a complex toolset.

^=== 5

Here is [a link to play the video on the YouTube page](https://youtu.be/vFjXKOXdgGo)

^= = ,,,, 12

Also,  look in the markdown to see how left column takes 7 of 12 cols, and this one takes 5 of 12!

^=

^=

#

Regular text to go below, [this is a link][1] like a lorem ipsum but I took the time to type it and autocorrect ruined it.
Example using \`<kdb>\` tags: Hit <kbd>ctrl-i</kbd> to do cool stuff (<kbd>command-i</kbd> on Mac).

[1]:https://google.com


### Heading 3

Inside a \`![\` screenshot you can add a \`^\` add then a \`+\`, \`-\` and,or \`o\`. + means float right, - means float left, and o means rounded circle. Below is regular \`![...]\`

![^CS logo](https://asullivan.csc.flcc.edu/lms-content/pics/discord-cs-logo-small.png)

![^+oCS logo](https://asullivan.csc.flcc.edu/lms-content/pics/discord-cs-logo-small.png)
Additional text that will be beside it, if the image is small enough, because - means float left and + means float right. In this case it is \`![^+o...]\` Note the \`o\` causes the rounded circle.

![^-CS logo](https://asullivan.csc.flcc.edu/lms-content/pics/discord-cs-logo-small.png)
This needed some work with margins/padding. Used the mr-3 in class, Bootstrap's handy way of quick notation for margin right "standard". For the right float it is ml-3. (mx is horiz, my is vertical)  So, this one was \`![^-...]\` to float left but no rounded circle. The image below is just adding the rounded circle with no float (large images will be responsive, not just centered) So, that is \`![^o...]\`

![^oCS logo](https://asullivan.csc.flcc.edu/lms-content/pics/discord-cs-logo-small.png)

^= =

#### Heading 4

Regular text to go below, like a lorem ipsum but I took the time to type it and autocorrect ruined it.

^===

This is a nested double column! A new possibility...

^= = ,,

##### Heading 5

Regular text to go below, like a lorem ipsum but I took the time to type it and autocorrect ruined it.


^=== ,,,

###### Heading 6

Regular text to go below, like a lorem ipsum but I took the time to type it and autocorrect ruined it.

^=

^=

^= =

## Bulleted List Test

- This an item
    - This is an indented item
    - at the same level
- back to top level
    - back to secondary
        - now tertiary
- How will this mix with numbers, I wonder...

^===

## Code Test

\`\`\`java
import java.util.Scanner;

public class Test {
    public static void main(String[] args) {
        Scanner scan = new Scanner(System.in);
        System.out.println("Enter a number");
        int number = scan.nextInt();
        System.out.println(number);
    }
}
\`\`\`

^=

## Numbered List Test

1. First level
    - bullet under number
2. First Level but second entry
3. Another number right below
    - with bullet underneath
        - and a second indent bullet
4. Back to first level, numbered

## Bullet with sub numbered list

- This is a test of numbering as child of bullet
    1. this
    2. second this
        - with bullets under
        - bullet
    3. third this
- back to first level bullet
    - second level bullet
        1. more
        2. numbers
                    
`
    },
    {
        title: `Tables`,
        md: `^= =

### Table Example

|Class |Return   |Status  |
|:----:|--------:|:-------|
| A    |    $5.00| final  |
| C    | $1012.00| ongoing|

#

markdown:
\`\`\`
|Class |Return   |Status  |
|:----:|--------:|:-------|
| A    |    $5.00| final  |
| C    | $1012.00| ongoing|
\`\`\`

^=^ ,,,, 12

Note the \`:\` on both sides means to center column and the \`:\` on left or right for left or right alignment.

^=

^===

### With Caption

|Class |Return   |Status  |
|:----:|--------:|:-------|
| A    |    $5.00| final  |
| C    | $1012.00| ongoing ^cap class return comparison^

markdown:
\`\`\`
|Class |Return   |Status  |
|:----:|--------:|:-------|
| A    |    $5.00| final  |
| C    | $1012.00| ongoing ^cap class return comparison^
\`\`\`

^=^ ,,,, 12 

For the caption, notice that the final \`|\` is removed when the \`^cap\` is added at the end and you need a final \`^\` at the end of the caption text.

^=

^=

^=^ ,,

You can [use this page to generate markdown tables][1], but typing it in manually is relatively quick, too.

[1]:https://www.tablesgenerator.com/markdown_tables#

^=`
    },
    {
        title: `Accordion & Tabs`,
        md: `^=^ ,,,

**Accordion & Tab Group Example**

Quickly create an ___interactive___ accordion or tab group that allows the visitor to reveal details while seeing an overall structure to the information.

You will need to paste the generated html into Brightspace to see it in action; it is formatted differently here so that no content is hiding while you preview.

^=

# Game Development Framework

^= = 7

^acc

^^^ 1. Create the Prototype

**Find the fun!** Typically a small cross-functional team of designers, programmers, and artists iterate on functional but incomplete versions of the game.  Agile methodology is a nice fit here. Part of ***planning***. 

^^^ 2. Establish the Requirements

Estimate the scope of the final game and then the time, money, and resources that will be necessary to complete it. People are the most important resource! Part of ***planning***. 

^^^ 3. Assemble the Team

Select and/or hire a team capable of completing the game. Choose leads in each department. Prepare to keep everyone happy and motivated as changes in personnel and responsibilities change. Part of ***planning***. 

^^^ 4. Make the Game

Implement the plan. Monitor progress and morale of the team. Make adjustments, maintain relationships with contractors, work through quality assurance (QA) and finish the game.

^^^ 5. Launch the Game

live operations, feedback, bugs, long-term health. Also involves ***planning!***

^acc-end

^=== 5

## Production Stages

^= = ,,,, 12

### Pre-Production

1. Create the prototype
    - mini-production!
2. Establish the Requirements
3. Assemble the Team

### Production

4. Make the Game

### Post-Production

5. Launch the Game

^=

^=

---

# Pre-Production Planning

^tabs 1. Create the Prototype, 2. Establish the Requirements, 3. Assemble the Team

**Find the fun!** Typically a small cross-functional team of designers, programmers, and artists iterate on functional but incomplete versions of the game.  Agile methodology is a nice fit here. (Note that there is no markdown \`^tab\` for the first tab it is only for dividers).

^tab

^= = 8

Estimate the scope of the final game and then the time, money, and resources that will be necessary to complete it. People are the most important resource!

^=== ,,,, 4

Rows with columns can be nested, so you can make each tab complex.

^=

^tab

Select and/or hire a team capable of completing the game. Choose leads in each department. Prepare to keep everyone happy and motivated as changes in personnel and responsibilities change.

^tabs-end
`
    },
    {
        title: `More Callout Ideas`,
        md: `More Callout Ideas
=============

^callout backward

### Rewind

Remember chapter 1 and the Game Production Framework.

[Go to module one](https://linktomodule1)

^callout-end

^callout balance-scale

### Balance

Even though this is an important aspect to focus on, don't forget the others! Too much focus on one area can create something truly lopsided and unsatisfying.

^callout-end

^callout comments

### Discuss

This is just the beginning of understanding. In a diverse group, discussion will tease out much more subtle and important necessary changes.

^callout-end

^callout headphones-alt

### Help Desk has Headphones!

To listen to tutorials, sounds, or music during class, a bluetooth headset will not work (lab computers don't have bluetooth!), so go ask for a pair of wired headphones at the Help Desk!

^callout-end

^callout user-graduate

### Building Towards Your degree

You will need to earn a ___C or better___ in this course to continue in the sequence of courses necessary to earn your degree in Computer Science, Computing Information Systems, or Game Programming and Design.

^callout-end

^callout book-reader

### Deepen Your Knowledge

Here are some great reads that cover this week's subject in much more detail:

- [Blood, Sweat, and Pixels by Jason Schreier](https://www.harpercollins.com/products/blood-sweat-and-pixels-jason-schreier?variant=32122042744866)


^callout-end

^callout file-archive

### Zip Your Projects

Remember to compress your project folder into a .zip file before turning it in!


^callout-end
        
` 

    },
    {
        title: "color test",
        md: `^^ BANNER

Big Title
=========

Color Title
-----------

^= = ,,,,

Test

^=== ,,

Test [with a link](https://google.com)

^=== ,,,

Test

^===

Test

^=

^callout book

This is some info about stuff

^callout-end

1. Test ^large^
2. More

And block

1. Test 2 ^box^
`
    },
    {
        title: "code assignment",
        md: `^^ LAB - Hello World

## DESCRIPTION

Create an entire HelloWorld class with a main method that displays the greeting on its own line:

\`\`\`
Hello, World!
\`\`\`

## SPECIFICATIONS

* File name: \`HelloWorld.java\`
* Be sure to add your name at the top of your program using comments.  (ie.   \`//Jon Smith\`  )
* Be sure to output the exact message as shown in the sample output, including letter case and punctuation.
* Be sure to line up the curly braces as shown in the text. ([Â§1.3â€‹ â€‹Aâ€‹ â€‹Program: Structured Code](https://docs.google.com/document/d/1m7s4QfQ1JRQ9aMCzxMOgj2fYl-8o4pKK5SFKo_gQoBg/edit#heading=h.tpyz13d7o67t))

## SAMPLE OUTPUT

\`\`\`
Hello, World!
\`\`\`

\`\`\`java ^copy
/**
 * Header Comment <Description here>
 * @author <name here>
 * @since <date here>
 */
public class HelloWorld {

//main method and output code goes here

}
/*
What was the hardest part?

What do you want to remember or what did you learn?

*/
\`\`\`
`
    },

];
addExamples();

function addExamples() {
    $selectExample.innerHTML = '';
    for (const ex of examples) {
        let opt = document.createElement('option');
        opt.value = ex.title;
        opt.textContent = ex.title;
        $selectExample.appendChild(opt);
    }
}

function changeExample() {
    let value = $selectExample.value;
    for (const ex of examples) {
        if (ex.title == value) {
            $markdownTextArea.value = ex.md;
            convert();
            break;
        }
    }
}

function addMetaCopyTypes() {
    $selectMetaCopy.innerHTML = '';
    for (const i of metaItems) {
        let opt = document.createElement('option');
        opt.value = i.type;
        opt.textContent = i.type;
        $selectMetaCopy.appendChild(opt);
    }
}

function copyMetaContent() {
    let value = $selectMetaCopy.value;

    for (const i of metaItems) {
        if (i.type == value) {
            //console.log(`${i.type} attempting to copy ${i.content}`);
            navigator.clipboard.writeText(i.content).then(
                function() {
                    console.log(`${i.type} content copied to clipboard`);
                })
            .catch (
                function() {
                    console.log(`unable to copy ${i.type} to clipboard`);
                }
            )
            break;
        }
    }
}

const colorFromMarkdownIndex = 1;
let colorThemes = [
    { 
        title: `Default`,
        accentBg:  defaultAccentBgColor,
        accentText: defaultAccentTextColor,
        accentLink: defaultAccentLinkColor,
    },
    {
        title: `from markdown`,
        accentBg:  defaultAccentBgColor,
        accentText: defaultAccentTextColor,
        accentLink: defaultAccentLinkColor,
    },
    {
        title: `Yellow`,
        accentBg: `#f1c232`,
        accentText: defaultAccentTextColor,
        accentLink: `#cef`,
    },
    {
        title: `Green`,
        accentBg: `#6aa84f`,
        accentText: defaultAccentTextColor,
        accentLink: `#cef`,
    },
    {
        title: `Red`,
        accentBg: `#cc0000`,
        accentText: defaultAccentTextColor,
        accentLink: `#cef`,
    },    
    {
        title: `Purple`,
        accentBg: `#a64d79`,
        accentText: defaultAccentTextColor,
        accentLink: `#cef`,
    },        
    {
        title: `Dodger Blue`,
        accentBg: `#1e90ff`,
        accentText: `#fff`,
        accentLink: `#fce`,
    },
    {
        title: `Gray, Tundora - D`,
        accentBg: `#434343`,
        accentText: `#fff`,
        accentLink: `#fce`,
    }, 
    {
        title: `Green, Hunter - 117 Python D`,
        accentBg: `#355e3B`,
        accentText: `#fff`,
        accentLink: `#fce`,
    },    
    {
        title: `Purple, Plumb - 103 Portal D`,
        accentBg: `#781346`,
        accentText: defaultAccentTextColor,
        accentLink: `#dde`,
    },
    {
        title: `Orange, Red - 141 Game A`,
        accentBg: `#bf2d00`,
        accentText: defaultAccentTextColor,
        accentLink: `#dde`,
    },
    {
        title: `Orange, International`,
        accentBg: `#ff4f00`,
        accentText: defaultAccentTextColor,
        accentLink: `#dde`,
    },
    {
        title: `Red Berry - 121 EdTech D`,
        accentBg: `#940000`,
        accentText: defaultAccentTextColor,
        accentLink: `#fce`,
    },   
    {
        title: `Red, Dark - 241 Game A`,
        accentBg: `#b11`,
        accentText: defaultAccentTextColor,
        accentLink: `#fce`,
    },
    {
        title: `Red, Bright`,
        accentBg: `#d11`,
        accentText: `#fff`,
        accentLink: `#fce`,
    },
    {
        title: `Yellowish`,
        accentBg: `#e6b800`,
        accentText: `#000`,
        accentLink: `#900`,
    },

];
addColorThemes();

function addColorThemes() {
    $selectColorTheme.innerHTML = '';
    for (const c of colorThemes) {
        let opt = document.createElement('option');
        opt.value = c.title;
        opt.textContent = c.title;
        $selectColorTheme.appendChild(opt);
    }
}

function changeColorTheme() {
    let value = $selectColorTheme.value;
    for (const c of colorThemes) {
        if (c.title == value) {
            accentBgColor = c.accentBg;
            accentTextColor = c.accentText;
            accentLinkColor = c.accentLink;
            updateStyle();
            convert();
            break;
        }
    }
}
