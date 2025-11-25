// assets/app.js

document.addEventListener('DOMContentLoaded', function() {
    
    // Получаем ID текущей группы из скрытого поля
    const activeGroupInput = document.getElementById('activeGroupId');
    
    // Если группа не выбрана, ничего не делаем
    if (!activeGroupInput) return;

    const groupId = activeGroupInput.value;

    // --- ФУНКЦИЯ: ЗАГРУЗКА УЧАСТНИКОВ ---
    function loadMembers() {
        fetch('api/get_members.php?group_id=' + groupId)
            .then(response => response.json())
            .then(data => {
                const listContainer = document.getElementById('members-list');
                if (data.length > 0) {
                    // Превращаем массив ['User1', 'User2'] в строку "User1, User2"
                    listContainer.textContent = data.join(', ');
                } else {
                    listContainer.textContent = 'Нет участников';
                }
            })
            .catch(err => console.error('Ошибка загрузки участников:', err));
    }

    // --- ФУНКЦИЯ: ЗАГРУЗКА ТОВАРОВ (Пока заглушка, сделаем на след. шаге) ---
    function loadItems() {
        // Здесь будет код для товаров
        document.getElementById('shoppingList').textContent = "Список товаров обновляется...";
    }

    // --- ЗАПУСК ---
    
    // 1. Загружаем сразу при открытии
    loadMembers();
    // loadItems(); // раскомментируем позже

    // 2. Ставим таймер на обновление каждые 3 секунды (3000 мс)
    setInterval(() => {
        loadMembers();
        // loadItems(); 
    }, 3000);
    
});