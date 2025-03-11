<?php
// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Получаем данные секции, если это редактирование
$sectionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $sectionId > 0;

if ($isEdit) {
    // Получаем данные секции для редактирования
    $stmt = $pdo->prepare("SELECT * FROM content WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $sectionId);
    $stmt->execute();
    $section = $stmt->fetch();
    
    if (!$section) {
        // Если секция не найдена, перенаправляем на страницу управления контентом
        header('Location: index.php?action=content');
        exit;
    }
    
    $pageKey = $section['page'];
    $sectionType = $section['section'];
    $title = $section['title'];
    $content = $section['content'];
    $imagePath = $section['image_path'];
    $sortOrder = $section['sort_order'];
} else {
    // Новая секция
    $pageKey = isset($_GET['page']) ? $_GET['page'] : 'main';
    $sectionType = '';
    $title = '';
    $content = '';
    $imagePath = '';
    
    // Определяем максимальный порядок сортировки для страницы
    $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM content WHERE page = :page");
    $stmt->bindParam(':page', $pageKey);
    $stmt->execute();
    $result = $stmt->fetch();
    $sortOrder = ($result && $result['max_order']) ? $result['max_order'] + 1 : 1;
}

// Получаем список страниц
$pages = [
    'main' => 'Главная страница',
    'hotel' => 'Гостиница',
    'sauna' => 'Сауна',
    'banquet' => 'Банкетный зал',
    'contacts' => 'Контакты'
];

// Получаем список типов секций
$sectionTypes = [
    'hero' => 'Главный баннер',
    'about' => 'О нас',
    'services' => 'Услуги',
    'rooms' => 'Номера',
    'gallery' => 'Галерея',
    'features' => 'Особенности',
    'testimonials' => 'Отзывы',
    'description' => 'Описание',
    'info' => 'Информация',
    'contacts' => 'Контакты',
    'map' => 'Карта'
];
?>

<div class="content-wrapper">
    <div class="content-header">
        <h1><?php echo $isEdit ? 'Редактирование секции' : 'Добавление новой секции'; ?></h1>
        <p>Управление содержимым секции страницы</p>
    </div>
    
    <div class="content-body">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="index.php?action=<?php echo $isEdit ? 'update_section&id=' . $sectionId : 'save_section'; ?>" method="post" enctype="multipart/form-data" class="section-form">
            <div class="form-group">
                <label for="page">Страница:</label>
                <select name="page" id="page" class="form-control" <?php echo $isEdit ? 'disabled' : ''; ?> required>
                    <?php foreach ($pages as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($pageKey === $key) ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="page" value="<?php echo htmlspecialchars($pageKey); ?>">
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="section">Тип секции:</label>
                <select name="section" id="section" class="form-control" <?php echo $isEdit ? 'disabled' : ''; ?> required>
                    <option value="">Выберите тип секции</option>
                    <?php foreach ($sectionTypes as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($sectionType === $key) ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="section" value="<?php echo htmlspecialchars($sectionType); ?>">
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="content">Содержимое:</label>
                <textarea name="content" id="content" class="form-control" rows="10"><?php echo htmlspecialchars($content); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="sort_order">Порядок сортировки:</label>
                <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?php echo (int)$sortOrder; ?>" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="image">Изображение:</label>
                <?php if (!empty($imagePath)): ?>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Текущее изображение" style="max-width: 200px;">
                        <p>Текущее изображение</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" id="image" class="form-control-file">
                <small class="form-text text-muted">Оставьте пустым, если не хотите менять изображение.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> <?php echo $isEdit ? 'Сохранить изменения' : 'Добавить секцию'; ?>
                </button>
                <a href="index.php?action=content&page=<?php echo $pageKey; ?>" class="btn btn-secondary">
                    <i class="fa fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
</div> 