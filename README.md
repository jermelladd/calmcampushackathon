# calmcampushackathon
# Project Overview

This project leverages various external libraries, OpenAI assistance, and custom PHP and JavaScript integration to deliver an interactive user experience for both regular users and site administrators.

## External Libraries

The following external libraries are used in this project to handle UI components, styling, and functionality:

- jQuery UI
- Bootstrap 4
- Font Awesome
- Google Fonts (Lato)
- Bootstrap Icons
- DataTables
- TinyMCE
- Google Charts

## Features

- **OpenAI Integration**: The project uses OpenAI for AI assistance and AI completions, allowing dynamic and intelligent interactions within the application.
  
- **PHP and JavaScript**: All JavaScript is processed within PHP web pages to create a seamless integration between server-side and client-side operations.

- **Custom Styling**: Custom styles are defined in the `/src` directory, specifically in `style.css` and `style-dash.css`, to ensure consistent design across the user interface and admin dashboards.

## Pages

- **`home.php`**: This is the main user homepage, providing access to core functionalities.
  
- **`webai-fe-dash.php`**: This is the site admin page, giving the admin control over site management and data insights.

## How to Use

1. Ensure all necessary libraries are loaded, including jQuery, Bootstrap, Font Awesome, DataTables, and TinyMCE.
2. OpenAI API should be properly configured for AI completions and assistance.
3. Custom styles and JavaScript files are located in the `/src` directory and can be modified to suit project requirements.
4. The entry point for regular users is `home.php`, while administrators can manage the site from `webai-fe-dash.php`.

