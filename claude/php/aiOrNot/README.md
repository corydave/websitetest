# Survey Rating App (1-10)

A simple, elegant PHP application for collecting and displaying rating survey results.

## Features

- ✨ Clean, responsive design with shadow effects
- 🎨 Uses #0046ad as the primary brand color
- ♿ Accessible markup with ARIA labels
- 📊 Animated bar chart results
- 📱 Mobile-friendly responsive layout
- 💾 JSON-based data storage (no database required)

## Files Included

- `index.php` - Main voting page with 1-10 number selection
- `vote.php` - Handles vote submission and data storage
- `results.php` - Displays results as a bar chart
- `styles.css` - All styling with box shadows and animations
- `script.js` - Client-side interactivity
- `data.json` - Vote data storage (includes sample data)

## Setup Instructions

1. **Upload files** to your web server
2. **Ensure PHP 7.0+** is installed
3. **Set permissions** on data.json to be writable:
   ```bash
   chmod 664 data.json
   ```
4. Visit `index.php` in your browser

## How It Works

1. **Voting Page** (`index.php`):
   - User sees numbers 1-10 in circular buttons
   - Clicks to select a number
   - Submit button appears with fade-in animation
   - Submits to vote.php

2. **Vote Processing** (`vote.php`):
   - Validates the submitted rating
   - Updates vote counts in data.json
   - Redirects to results page

3. **Results Page** (`results.php`):
   - Shows total response count
   - Displays bar chart for each number (1-10)
   - Bars animate in with smooth transitions
   - Link to vote again

## Customization

### Colors
To change the primary color from #0046ad, update in `styles.css`:
- Search for `#0046ad` and replace with your color
- Also update `#003a8f` (hover state) to a darker shade

### Design
- Box shadows are defined in `.survey-box` and `.results-box`
- Border radius is set to 12px for containers, 25px for buttons
- Number circles are 60px on desktop, 50px on mobile

## Data Storage

Votes are stored in `data.json` in this format:
```json
{
    "total": 33,
    "votes": {
        "1": 0,
        "2": 1,
        "3": 3,
        ...
    }
}
```

### Resetting Data
To reset all votes, replace data.json contents with:
```json
{
    "total": 0,
    "votes": {
        "1": 0,
        "2": 0,
        "3": 0,
        "4": 0,
        "5": 0,
        "6": 0,
        "7": 0,
        "8": 0,
        "9": 0,
        "10": 0
    }
}
```

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11 with graceful degradation
- Mobile browsers (iOS Safari, Chrome Mobile)

## Accessibility Features

- Semantic HTML structure
- ARIA labels for screen readers
- Keyboard navigation support
- Focus indicators on interactive elements
- Sufficient color contrast

## Notes

- No database required - uses JSON file storage
- Sample data included to match your mockup (33 responses)
- Fully responsive from mobile to desktop
- Smooth animations for better UX

Enjoy your survey app! 🎉
