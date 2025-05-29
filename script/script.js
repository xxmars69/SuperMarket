function toggleSidebar() {
    document.body.classList.toggle('sidebar-open');
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
}

document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.getElementById('table-dropdown');
    fetch('get_tables.php')
        .then(res => res.json())
        .then(data => {
            dropdown.innerHTML = '<option value="" disabled selected>Choose a table...</option>';
            if (data.tables) {
                data.tables.forEach(table => {
                    const option = document.createElement('option');
                    option.value = table;
                    option.textContent = table;
                    dropdown.appendChild(option);
                });
            } else {
                dropdown.innerHTML = '<option disabled>Error loading tables</option>';
            }
        })
        .catch(err => {
            console.error('Error fetching tables:', err);
            dropdown.innerHTML = '<option disabled>Error loading tables</option>';
        });
});

document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.getElementById('table-dropdown');

    fetch('get_tables.php')
        .then(res => res.json())
        .then(data => {
            dropdown.innerHTML = '<option value="" disabled selected>Choose a table...</option>';
            data.tables.forEach(table => {
                const option = document.createElement('option');
                option.value = table;
                option.textContent = table;
                dropdown.appendChild(option);
            });
        })
        .catch(() => {
            dropdown.innerHTML = '<option disabled>Error loading tables</option>';
        });

});


let cpuChart, dbChart, memoryChart;

function createCharts(cpu, db, mem) {
    const options = {
        type: 'doughnut',
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    };

    cpuChart = new Chart(document.getElementById('cpuChart'), {
        ...options,
        data: {
            labels: ['Used', 'Free'],
            datasets: [{
                data: [cpu, 100 - cpu],
                backgroundColor: ['#ff6384', '#dddddd']
            }]
        }
    });

    dbChart = new Chart(document.getElementById('dbChart'), {
        ...options,
        data: {
            labels: ['Active', 'Idle'],
            datasets: [{
                data: [db, 100 - db],
                backgroundColor: ['#36a2eb', '#dddddd']
            }]
        }
    });

    memoryChart = new Chart(document.getElementById('memoryChart'), {
        ...options,
        data: {
            labels: ['Used', 'Free'],
            datasets: [{
                data: [mem, 100 - mem],
                backgroundColor: ['#ffcd56', '#dddddd']
            }]
        }
    });
}



function updateCharts(cpu, db, mem) {
    cpuChart.data.datasets[0].data = [cpu, 100 - cpu];
    dbChart.data.datasets[0].data = [db, 100 - db];
    memoryChart.data.datasets[0].data = [mem, 100 - mem];

    cpuChart.update();
    dbChart.update();
    memoryChart.update();
}

function fetchMetrics() {
    $.getJSON('dashboard.php?metrics=1', function (data) {
        if (!cpuChart) {
            createCharts(data.cpu, data.db, data.memory);
        } else {
            updateCharts(data.cpu, data.db, data.memory);
        }
    });
}


document.addEventListener("DOMContentLoaded", function () {
    fetchMetrics();
    setInterval(fetchMetrics, 5000);
});


  
    