document.addEventListener("DOMContentLoaded", function() {
    const chartDataEl = document.getElementById('chartData');
    const canvas = document.getElementById('salesChart');

    if (chartDataEl && canvas) {
        const ctx = canvas.getContext('2d');

        // --- THIS IS THE NEW, CORRECT FIX ---
        // Get any chart that might already be attached to this canvas.
        let existingChart = Chart.getChart(ctx);
        
        // If an old chart exists, destroy it before we create a new one.
        if (existingChart) {
            existingChart.destroy();
        }
        // --- END OF FIX ---

        // Get the data from our PHP script
        const labels = JSON.parse(chartDataEl.dataset.labels);
        const values = JSON.parse(chartDataEl.dataset.values);
        
        // Create the new chart instance.
        const mySalesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Sales ($)',
                    data: values,
                    backgroundColor: 'rgba(74, 105, 189, 0.2)',
                    borderColor: 'rgba(74, 105, 189, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
});