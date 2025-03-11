<?php
// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Получаем список страниц
$pages = [
    'main' => [
        'title' => 'Главная страница',
        'template' => '../templates/main.html',
        'output' => '../public/index.html'
    ],
    'hotel' => [
        'title' => 'Гостиница',
        'template' => '../templates/hotel.html',
        'output' => '../public/pages/hotel.html'
    ],
    'sauna' => [
        'title' => 'Сауна',
        'template' => '../templates/sauna.html',
        'output' => '../public/pages/sauna.html'
    ],
    'banquet' => [
        'title' => 'Банкетный зал',
        'template' => '../templates/banquet.html',
        'output' => '../public/pages/banquet.html'
    ],
    'contacts' => [
        'title' => 'Контакты',
        'template' => '../templates/contacts.html',
        'output' => '../public/pages/contacts.html'
    ]
];
?>

<div class="content-wrapper">
    <div class="content-header">
        <h1>Генерация сайта</h1>
        <p>Создание статических HTML-страниц на основе контента из базы данных</p>
    </div>
    
    <div class="content-body">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info">
            <p><i class="fa fa-info-circle"></i> После внесения изменений в контент или настройки сайта, необходимо сгенерировать статические страницы для применения изменений на сайте.</p>
        </div>
        
        <div class="generate-options">
            <h3>Выберите страницы для генерации:</h3>
            
            <form action="index.php?action=generate_site" method="post" class="generate-form">
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="select_all" class="form-check-input" checked>
                        <label for="select_all" class="form-check-label">Выбрать все</label>
                    </div>
                </div>
                
                <div class="pages-list">
                    <?php foreach ($pages as $pageKey => $pageData): ?>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="pages[]" id="page_<?php echo $pageKey; ?>" 
                                       value="<?php echo $pageKey; ?>" class="form-check-input page-checkbox" checked>
                                <label for="page_<?php echo $pageKey; ?>" class="form-check-label">
                                    <?php echo $pageData['title']; ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-sync"></i> Сгенерировать выбранные страницы
                    </button>
                </div>
            </form>
        </div>
        
        <div class="deploy-options">
            <h3>Деплой на Netlify:</h3>
            
            <div class="alert alert-warning">
                <p><i class="fa fa-exclamation-triangle"></i> После генерации страниц необходимо выполнить деплой на Netlify для публикации изменений.</p>
            </div>
            
            <form action="index.php?action=deploy_netlify" method="post" class="deploy-form">
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-cloud-upload-alt"></i> Выполнить деплой на Netlify
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для "Выбрать все"
    const selectAllCheckbox = document.getElementById('select_all');
    const pageCheckboxes = document.querySelectorAll('.page-checkbox');
    
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        pageCheckboxes.forEach(function(checkbox) {
            checkbox.checked = isChecked;
        });
    });
    
    // Обновление "Выбрать все" при изменении отдельных чекбоксов
    pageCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(pageCheckboxes).every(function(cb) {
                return cb.checked;
            });
            selectAllCheckbox.checked = allChecked;
        });
    });
});
</script> 