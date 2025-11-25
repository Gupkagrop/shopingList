// assets/app.js

document.addEventListener('DOMContentLoaded', function() {
    
    const activeGroupInput = document.getElementById('activeGroupId');
    if (!activeGroupInput) return;

    const groupId = activeGroupInput.value;
    let currentItems = [];

    // --- ФУНКЦИЯ: ЗАГРУЗКА УЧАСТНИКОВ ---
    function loadMembers() {
        fetch('api/get_members.php?group_id=' + groupId)
            .then(response => response.json())
            .then(data => {
                const listContainer = document.getElementById('members-list');
                if (data.length > 0) {
                    listContainer.textContent = data.join(', ');
                } else {
                    listContainer.textContent = 'Нет участников';
                }
            })
            .catch(err => console.error('Ошибка загрузки участников:', err));
    }

    // --- ФУНКЦИЯ: ЗАГРУЗКА ТОВАРОВ ---
    function loadItems() {
        fetch('api/get_items.php?group_id=' + groupId)
            .then(response => response.json())
            .then(data => {
                currentItems = data;
                renderItems();
            })
            .catch(err => console.error('Ошибка загрузки товаров:', err));
    }

    // --- ФУНКЦИЯ: ОТОБРАЖЕНИЕ ТОВАРОВ ---
    function renderItems() {
        const container = document.getElementById('shoppingList');
        
        if (currentItems.length === 0) {
            container.innerHTML = '<p>Список покупок пуст. Добавьте первый товар!</p>';
            return;
        }

        const itemsHTML = currentItems.map(item => `
            <div class="item ${item.is_bought ? 'bought' : ''}" style="
                padding: 10px; 
                margin: 5px 0; 
                border: 1px solid #ddd; 
                border-radius: 5px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                ${item.is_bought ? 'background: #f0f8f0; text-decoration: line-through;' : ''}
            ">
                <div>
                    <strong>${escapeHtml(item.name)}</strong>
                    <small style="color: #666;"> (добавил: ${escapeHtml(item.added_by)})</small>
                </div>
                <div>
                    <button onclick="toggleItem(${item.id})" class="toggle-btn" style="
                        margin-right: 5px;
                        background: ${item.is_bought ? '#ffa500' : '#4CAF50'};
                        color: white;
                        border: none;
                        padding: 5px 10px;
                        border-radius: 3px;
                        cursor: pointer;
                    ">
                        ${item.is_bought ? '❌ Не куплено' : '✅ Куплено'}
                    </button>
                    <button onclick="deleteItem(${item.id})" class="delete-btn" style="
                        background: #ff4444;
                        color: white;
                        border: none;
                        padding: 5px 10px;
                        border-radius: 3px;
                        cursor: pointer;
                    ">
                        🗑️ Удалить
                    </button>
                </div>
            </div>
        `).join('');

        container.innerHTML = itemsHTML;
    }

    // --- ФУНКЦИЯ: ДОБАВЛЕНИЕ ТОВАРА ---
    function addItem() {
        const input = document.getElementById('newItemInput');
        const itemName = input.value.trim();

        if (!itemName) {
            alert('Введите название товара');
            return;
        }

        const formData = new FormData();
        formData.append('group_id', groupId);
        formData.append('item_name', itemName);

        fetch('api/add_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                loadItems(); // Перезагружаем список
            } else {
                alert('Ошибка: ' + data.error);
            }
        })
        .catch(err => {
            console.error('Ошибка добавления товара:', err);
            alert('Ошибка при добавлении товара');
        });
    }

    // --- ФУНКЦИЯ: ПЕРЕКЛЮЧЕНИЕ СТАТУСА ---
    window.toggleItem = function(itemId) {
        const formData = new FormData();
        formData.append('item_id', itemId);

        fetch('api/toggle_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadItems(); // Перезагружаем список
            } else {
                alert('Ошибка: ' + data.error);
            }
        })
        .catch(err => {
            console.error('Ошибка изменения статуса:', err);
            alert('Ошибка при изменении статуса');
        });
    }

    // --- ФУНКЦИЯ: УДАЛЕНИЕ ТОВАРА ---
    window.deleteItem = function(itemId) {
        if (!confirm('Удалить этот товар?')) {
            return;
        }

        const formData = new FormData();
        formData.append('item_id', itemId);

        fetch('api/delete_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadItems(); // Перезагружаем список
            } else {
                alert('Ошибка: ' + data.error);
            }
        })
        .catch(err => {
            console.error('Ошибка удаления товара:', err);
            alert('Ошибка при удалении товара');
        });
    }

    // --- ВСПОМОГАТЕЛЬНАЯ ФУНКЦИЯ ---
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // --- НАСТРОЙКА СОБЫТИЙ ---
    document.getElementById('addItemBtn').addEventListener('click', addItem);
    document.getElementById('newItemInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            addItem();
        }
    });

    // --- ЗАПУСК ---
    loadMembers();
    loadItems();

    // Автообновление каждые 3 секунды
    setInterval(() => {
        loadMembers();
        loadItems();
    }, 3000);
});