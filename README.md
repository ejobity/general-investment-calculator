# general-investment-calculator
### Wordpress Plugin
#### Requires at least: 6.0
#### Tested up to: 6.8
#### Stable tag: 1.0.0
#### Requires PHP: 7.0
#### License: GPLv2 or later

A comprehensive investment calculator with compound interest and contribution calculations. This is a wordpress plugin.


## Key Features:

### Input Fields:

-   **Initial Investment Amount**: Text field with number input
-   **Investment Period**: Number input with dropdown for months/years
-   **Interest Rate**: Annual percentage rate input
-   **Compound Rate**: Dropdown with daily, weekly, monthly, quarterly, semi-annual, and annual options
-   **Contribution Amount**: Regular contribution input
-   **Contribution Frequency**: How often contributions are made

### Results Display:

1.  **Summary Cards**: Shows initial investment, total contributions, total returns, and final amount
2.  **Pie Chart**: Visual breakdown of initial investment vs contributions vs interest earned
3.  **Stacked Column Chart**: Growth over time showing the three components stacked
4.  **Accumulation Schedule Table**: Detailed period-by-period breakdown

### Technical Features:

-   **Bootstrap 5**: Modern, responsive design
-   **Chart.js**: Interactive charts with tooltips
-   **AJAX Processing**: Smooth calculations without page reload
-   **WordPress Integration**: Proper hooks, nonces for security
-   **Responsive Design**: Works on all device sizes
-   **Loading Indicators**: User-friendly loading states

## Installation Instructions:

1.  **Save the code** as `investment-calculator.php` in your WordPress plugins directory (`/wp-content/plugins/investment-calculator/`)
2.  **Create the plugin directory structure**:
    
    ```
    /wp-content/plugins/investment-calculator/
    └── investment-calculator.php
    ```
    
3.  **Activate the plugin** in your WordPress admin dashboard
4.  **Use the shortcode** `[investment_calculator]` on any page or post where you want the calculator to appear

## Usage:

-   Users can adjust all parameters using the intuitive form
-   Real-time calculations show comprehensive results
-   The pie chart visualizes the investment breakdown
-   The stacked chart shows growth progression over time
-   The accumulation table provides detailed period-by-period analysis

The plugin handles complex compound interest calculations with regular contributions and provides professional-grade visualizations that will help users understand their investment growth potential.

![enter image description here](https://loanfren.com/wp-content/uploads/2025/08/investment-calculator.jpg)
