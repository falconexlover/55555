<?php
// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Получаем список страниц
$pages = [
    'main' => 'Главная страница',
    'hotel' => 'Гостиница',
    'sauna' => 'Сауна',
    'banquet' => 'Банкетный зал',
    'contacts' => 'Контакты'
];

// Получаем выбранную страницу
$selectedPage = isset($_GET['page']) ? $_GET['page'] : 'main';

// Получаем контент для выбранной страницы
$pageContent = getPageContent($pdo, $selectedPage);
?>

<div class="content-wrapper">
    <div class="content-header">
        <h1>Управление контентом</h1>
        <p>Редактирование содержимого страниц сайта</p>
    </div>
    
    <div class="content-body">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="page-selector">
            <h3>Выберите страницу для редактирования:</h3>
            <div class="page-tabs">
                <?php foreach ($pages as $pageKey => $pageTitle): ?>
                    <a href="index.php?action=content&page=<?php echo $pageKey; ?>" 
                       class="page-tab <?php echo ($selectedPage === $pageKey) ? 'active' : ''; ?>">
                        <?php echo $pageTitle; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="content-sections">
            <h3>Секции страницы "<?php echo $pages[$selectedPage]; ?>":</h3>
            
            <div class="add-section">
                <a href="index.php?action=add_section&page=<?php echo $selectedPage; ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Добавить новую секцию
                </a>
            </div>
            
            <?php if (empty($pageContent)): ?>
                <div class="alert alert-info">
                    На этой странице пока нет секций контента. Добавьте новую секцию.
                </div>
            <?php else: ?>
                <div class="sections-list">
                    <?php foreach ($pageContent as $section): ?>
                        <div class="section-item">
                            <div class="section-header">
                                <h4><?php echo htmlspecialchars($section['title']); ?></h4>
                                <div class="section-type">Тип: <?php echo htmlspecialchars($section['section']); ?></div>
                            </div>
                            
                            <div class="section-preview">
                                <?php if (!empty($section['image_path'])): ?>
                                    <div class="section-image">
                                        <img src="<?php echo htmlspecialchars($section['image_path']); ?>" alt="Изображение секции">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="section-content">
                                    <?php echo nl2br(htmlspecialchars(substr($section['content'], 0, 200))); ?>
                                    <?php if (strlen($section['content']) > 200): ?>...<?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="section-actions">
                                <a href="index.php?action=edit_section&id=<?php echo $section['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fa fa-edit"></i> Редактировать
                                </a>
                                <a href="index.php?action=delete_section&id=<?php echo $section['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Вы уверены, что хотите удалить эту секцию?');">
                                    <i class="fa fa-trash"></i> Удалить
                                </a>
                                
                                <?php if ($section['sort_order'] > 1): ?>
                                    <a href="index.php?action=move_section&id=<?php echo $section['id']; ?>&direction=up" class="btn btn-sm btn-secondary">
                                        <i class="fa fa-arrow-up"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="index.php?action=move_section&id=<?php echo $section['id']; ?>&direction=down" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-arrow-down"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 