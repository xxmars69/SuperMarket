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


  
    