<?php
/**
 * Plugin Name: General Investment Calculator
 * Description: A comprehensive investment calculator with compound interest and contribution calculations
 * Version: 1.0
 * Author: Emile Jobity
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class InvestmentCalculator {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('investment_calculator', array($this, 'render_calculator'));
        add_action('wp_ajax_calculate_investment', array($this, 'ajax_calculate_investment'));
        add_action('wp_ajax_nopriv_calculate_investment', array($this, 'ajax_calculate_investment'));
    }
    
    public function enqueue_scripts() {
        // Bootstrap CSS
        wp_enqueue_style('bootstrap-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css');
        
        // Chart.js
        wp_enqueue_script('chartjs', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', array(), '3.9.1', true);
        
        // Bootstrap JS
        wp_enqueue_script('bootstrap-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
        
        // Custom CSS
        wp_add_inline_style('bootstrap-css', $this->get_custom_css());
        
        // Localize script for AJAX
        wp_localize_script('jquery', 'investment_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('investment_calculator_nonce')
        ));
    }
    
    private function get_custom_css() {
        return "
            .investment-calculator {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .calculator-form {
                background: #f8f9fa;
                padding: 30px;
                border-radius: 10px;
                margin-bottom: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .results-section {
                background: white;
                padding: 30px;
                border-radius: 10px;
                margin-bottom: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .chart-container {
                position: relative;
                height: 400px;
                margin: 20px 0;
            }
            .results-summary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 25px;
                border-radius: 10px;
                margin-bottom: 30px;
            }
            .summary-item {
                text-align: center;
                padding: 15px;
            }
            .summary-amount {
                font-size: 2rem;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .summary-label {
                font-size: 0.9rem;
                opacity: 0.9;
            }
            .table-responsive {
                max-height: 500px;
                overflow-y: auto;
            }
            .loading-spinner {
                display: none;
                text-align: center;
                padding: 20px;
            }
        ";
    }
    
    public function render_calculator() {
        ob_start();
        ?>
        <div class="investment-calculator">
            <form id="investment-form" class="calculator-form">
                <h2 class="text-center mb-4 text-primary">Investment Calculator</h2>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="initial-investment" class="form-label">Initial Investment Amount ($)</label>
                            <input type="number" class="form-control" id="initial-investment" value="10000" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="investment-period" class="form-label">Investment Period</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="investment-period" value="10" min="1" required>
                                <select class="form-select" id="period-unit" style="max-width: 100px;">
                                    <option value="years">Years</option>
                                    <option value="months">Months</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="interest-rate" class="form-label">Annual Interest Rate (%)</label>
                            <input type="number" class="form-control" id="interest-rate" value="7" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="compound-rate" class="form-label">Compound Frequency</label>
                            <select class="form-select" id="compound-rate">
                                <option value="365">Daily</option>
                                <option value="52">Weekly</option>
                                <option value="12" selected>Monthly</option>
                                <option value="4">Quarterly</option>
                                <option value="2">Every 6 months</option>
                                <option value="1">Annually</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contribution-amount" class="form-label">Regular Contribution Amount ($)</label>
                            <input type="number" class="form-control" id="contribution-amount" value="500" step="0.01">
                        </div>
                        
                        <div class="mb-3">
                            <label for="contribution-frequency" class="form-label">Contribution Frequency</label>
                            <select class="form-select" id="contribution-frequency">
                                <option value="365">Daily</option>
                                <option value="52">Weekly</option>
                                <option value="12" selected>Monthly</option>
                                <option value="4">Quarterly</option>
                                <option value="2">Every 6 months</option>
                                <option value="1">Annually</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5">Calculate Investment</button>
                </div>
            </form>
            
            <div class="loading-spinner" id="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Calculating your investment...</p>
            </div>
            
            <div id="results-container" style="display: none;">
                <div class="results-summary">
                    <div class="row">
                        <div class="col-md-4 summary-item">
                            <div class="summary-amount" id="initial-amount-display">$0</div>
                            <div class="summary-label">Initial Investment</div>
                        </div>
                        <div class="col-md-4 summary-item">
                            <div class="summary-amount" id="total-contributions-display">$0</div>
                            <div class="summary-label">Total Contributions</div>
                        </div>
                        <div class="col-md-4 summary-item">
                            <div class="summary-amount" id="total-returns-display">$0</div>
                            <div class="summary-label">Total Returns</div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <div class="summary-amount" id="final-amount-display">$0</div>
                            <div class="summary-label">Final Amount</div>
                        </div>
                    </div>
                </div>
                
                <div class="results-section">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-center mb-3">Investment Breakdown</h4>
                            <div class="chart-container">
                                <canvas id="pie-chart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4 class="text-center mb-3">Growth Over Time</h4>
                            <div class="chart-container">
                                <canvas id="stacked-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="results-section">
                    <h4 class="mb-3">Accumulation Schedule</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="accumulation-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Period</th>
                                    <th>Beginning Balance</th>
                                    <th>Contributions</th>
                                    <th>Interest Earned</th>
                                    <th>Ending Balance</th>
                                </tr>
                            </thead>
                            <tbody id="accumulation-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let pieChart, stackedChart;
            
            $('#investment-form').on('submit', function(e) {
                e.preventDefault();
                
                $('#loading-spinner').show();
                $('#results-container').hide();
                
                const formData = {
                    action: 'calculate_investment',
                    nonce: investment_ajax.nonce,
                    initial_investment: $('#initial-investment').val(),
                    investment_period: $('#investment-period').val(),
                    period_unit: $('#period-unit').val(),
                    interest_rate: $('#interest-rate').val(),
                    compound_rate: $('#compound-rate').val(),
                    contribution_amount: $('#contribution-amount').val(),
                    contribution_frequency: $('#contribution-frequency').val()
                };
                
                $.ajax({
                    url: investment_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            displayResults(response.data);
                        } else {
                            alert('Error calculating investment: ' + response.data);
                        }
                        $('#loading-spinner').hide();
                    },
                    error: function() {
                        alert('Error processing request');
                        $('#loading-spinner').hide();
                    }
                });
            });
            
            function displayResults(data) {
                // Update summary
                $('#initial-amount-display').text('$' + numberWithCommas(data.initial_investment));
                $('#total-contributions-display').text('$' + numberWithCommas(data.total_contributions));
                $('#total-returns-display').text('$' + numberWithCommas(data.total_interest));
                $('#final-amount-display').text('$' + numberWithCommas(data.final_amount));
                
                // Create pie chart
                if (pieChart) pieChart.destroy();
                const pieCtx = document.getElementById('pie-chart').getContext('2d');
                pieChart = new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Initial Investment', 'Contributions', 'Interest Earned'],
                        datasets: [{
                            data: [data.initial_investment, data.total_contributions, data.total_interest],
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': $' + numberWithCommas(context.parsed);
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Create stacked chart
                if (stackedChart) stackedChart.destroy();
                const stackedCtx = document.getElementById('stacked-chart').getContext('2d');
                stackedChart = new Chart(stackedCtx, {
                    type: 'bar',
                    data: {
                        labels: data.chart_labels,
                        datasets: [{
                            label: 'Initial Investment',
                            data: data.chart_initial,
                            backgroundColor: '#FF6384',
                            borderWidth: 1
                        }, {
                            label: 'Contributions',
                            data: data.chart_contributions,
                            backgroundColor: '#36A2EB',
                            borderWidth: 1
                        }, {
                            label: 'Interest Earned',
                            data: data.chart_interest,
                            backgroundColor: '#FFCE56',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Time Period'
                                }
                            },
                            y: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Amount ($)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '$' + numberWithCommas(value);
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': $' + numberWithCommas(context.parsed.y);
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Update accumulation table
                updateAccumulationTable(data.schedule);
                
                $('#results-container').show();
            }
            
            function updateAccumulationTable(schedule) {
                const tbody = $('#accumulation-tbody');
                tbody.empty();
                
                schedule.forEach(function(row) {
                    tbody.append(`
                        <tr>
                            <td>${row.period}</td>
                            <td>$${numberWithCommas(row.beginning_balance)}</td>
                            <td>$${numberWithCommas(row.contributions)}</td>
                            <td>$${numberWithCommas(row.interest_earned)}</td>
                            <td>$${numberWithCommas(row.ending_balance)}</td>
                        </tr>
                    `);
                });
            }
            
            function numberWithCommas(x) {
                return parseFloat(x).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_calculate_investment() {
        if (!wp_verify_nonce($_POST['nonce'], 'investment_calculator_nonce')) {
            wp_die('Security check failed');
        }
        
        $initial_investment = floatval($_POST['initial_investment']);
        $investment_period = intval($_POST['investment_period']);
        $period_unit = sanitize_text_field($_POST['period_unit']);
        $interest_rate = floatval($_POST['interest_rate']) / 100;
        $compound_rate = intval($_POST['compound_rate']);
        $contribution_amount = floatval($_POST['contribution_amount']);
        $contribution_frequency = intval($_POST['contribution_frequency']);
        
        // Convert period to years
        $years = ($period_unit === 'months') ? $investment_period / 12 : $investment_period;
        $total_periods = intval($years * $compound_rate);
        
        // Calculate contributions per compounding period
        $contributions_per_period = ($contribution_amount * $contribution_frequency) / $compound_rate;
        
        // Calculate compound interest
        $rate_per_period = $interest_rate / $compound_rate;
        
        $schedule = array();
        $balance = $initial_investment;
        $total_contributions = 0;
        $total_interest = 0;
        
        // For charting
        $chart_labels = array();
        $chart_initial = array();
        $chart_contributions = array();
        $chart_interest = array();
        
        // Track data for different intervals for charting
        $chart_interval = max(1, floor($total_periods / 20)); // Show max 20 points on chart
        
        for ($period = 1; $period <= $total_periods; $period++) {
            $beginning_balance = $balance;
            $period_interest = $balance * $rate_per_period;
            $period_contributions = $contributions_per_period;
            
            $balance = $balance + $period_interest + $period_contributions;
            $total_contributions += $period_contributions;
            $total_interest += $period_interest;
            
            // Add to schedule (show yearly or every 12th period for monthly compounding)
            if ($compound_rate >= 12 && $period % 12 == 0 || $compound_rate < 12) {
                $schedule[] = array(
                    'period' => $period,
                    'beginning_balance' => round($beginning_balance, 2),
                    'contributions' => round($period_contributions * ($compound_rate >= 12 ? 12 : 1), 2),
                    'interest_earned' => round($period_interest * ($compound_rate >= 12 ? 12 : 1), 2),
                    'ending_balance' => round($balance, 2)
                );
            }
            
            // Add to chart data
            if ($period % $chart_interval == 0 || $period == $total_periods) {
                $chart_labels[] = 'Period ' . $period;
                $chart_initial[] = $initial_investment;
                $chart_contributions[] = round($total_contributions, 2);
                $chart_interest[] = round($total_interest, 2);
            }
        }
        
        $result = array(
            'initial_investment' => round($initial_investment, 2),
            'total_contributions' => round($total_contributions, 2),
            'total_interest' => round($total_interest, 2),
            'final_amount' => round($balance, 2),
            'schedule' => $schedule,
            'chart_labels' => $chart_labels,
            'chart_initial' => $chart_initial,
            'chart_contributions' => $chart_contributions,
            'chart_interest' => $chart_interest
        );
        
        wp_send_json_success($result);
    }
}

// Initialize the plugin
new InvestmentCalculator();

// Activation hook to create necessary database tables if needed
register_activation_hook(__FILE__, function() {
    // Add any activation code here if needed
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Add any cleanup code here if needed
});
?>