# Insolvenzo Form Plugin

A contemporary React-based WordPress plugin for Insolvenz public forms with PDF generation capabilities.

## Description

The **Insolvenzo Form Plugin** provides a public React-based form for insolvency procedures - specifically designed for generating "Bescheinigung Pfändungskonto" (garnishment account certificates) with integrated PDF export functionality.

## Features

- 📝 Dynamic React-based form component
- 📄 PDF generation and export
- 🎨 Gutenberg block integration
- ⚙️ Server-side rendering for accessibility
- 🌍 Multilingual support (German localization included)

## Installation

1. Download or clone this repository to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins
   git clone https://github.com/yourusername/insolvenzo-form.git
   ```

2. Install dependencies:
   ```bash
   cd insolvenzo-form
   npm install
   ```

3. Build the project:
   ```bash
   npm run build
   ```

4. Activate the plugin through the WordPress admin dashboard.

## Usage

### As a Gutenberg Block

1. Create or edit a post/page in the WordPress editor
2. Add the "Insolvenzo Form" block
3. Configure the block settings as needed

### Installation via ZIP

Alternatively, you can download the compiled plugin and upload it via WordPress admin:
1. Go to Plugins → Add New → Upload Plugin
2. Select the plugin ZIP file
3. Activate the plugin

## Development

### Prerequisites

- Node.js (v14 or higher)
- npm or yarn

### Setup

```bash
npm install
```

### Development Build

```bash
npm run dev
```

### Production Build

```bash
npm run build
```

## Project Structure

```
insolvenzo-form/
├── src/              # Source files (React components, styles)
├── build/            # Built/compiled files
├── package.json      # Project dependencies
├── insolvenzo-form.php  # Main plugin file
└── README.md         # This file
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Author

Janos

## Support

For issues, feature requests, or questions, please [create an issue](https://github.com/yourusername/insolvenzo-form/issues).

## Changelog

### 1.0.0
- Initial release
