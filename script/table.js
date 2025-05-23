const tableName = new URLSearchParams(window.location.search).get('name');

function loadTable(page = 1, search = null) {
    const formData = new FormData();
    formData.append('name', tableName);
    formData.append('page', page);
    if (search) {
        formData.append('use_proc', 1);
        formData.append('column', search.column);
        formData.append('value', search.value);
    }

    fetch('fetch_table_data.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }

        let headers = '<tr>';
        data.columns.forEach(col => {
            headers += `<th>${col}</th>`;
        });
        headers += '</tr>';
        document.getElementById('table-headers').innerHTML = headers;

        document.getElementById('table-body').innerHTML = data.body;

        let pagControls = '';
        if (page > 1) {
            pagControls += `<button onclick="loadTable(${page - 1})">← Prev</button>`;
        }
        pagControls += `<span style="margin:0 10px;">Page ${page}</span>`;
        pagControls += `<button onclick="loadTable(${page + 1})">Next →</button>`;
        document.getElementById('pagination-controls').innerHTML = pagControls;
    });
}

loadTable();

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('search-form');
    form.addEventListener('submit', e => {
        e.preventDefault();
        const column = form.querySelector("select[name='column']").value;
        const value = form.querySelector("input[name='value']").value;
        loadTable(1, { column, value });
    });
});