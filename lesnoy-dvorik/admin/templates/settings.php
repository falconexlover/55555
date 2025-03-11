<?php
// Проверка авторизации
if (!isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Получаем все настройки
$settings = getAllSettings($pdo);
?>

<div class="content-wrapper">
    <div class="content-header">
        <h1>Настройки сайта</h1>
        <p>Управление основными настройками сайта</p>
    </div>
    
    <div class="content-body">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="index.php?action=save_settings" method="post" class="settings-form">
            <div class="settings-section">
                <h3>Основные настройки</h3>
                
                <div class="form-group">
                    <label for="site_name">Название сайта:</label>
                    <input type="text" name="settings[site_name]" id="site_name" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="site_description">Описание сайта:</label>
                    <textarea name="settings[site_description]" id="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="settings-section">
                <h3>Контактная информация</h3>
                
                <div class="form-group">
                    <label for="contact_email">Email:</label>
                    <input type="email" name="settings[contact_email]" id="contact_email" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_phone">Телефон:</label>
                    <input type="text" name="settings[contact_phone]" id="contact_phone" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_address">Адрес:</label>
                    <textarea name="settings[contact_address]" id="contact_address" class="form-control" rows="3"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="settings-section">
                <h3>Социальные сети</h3>
                
                <div class="form-group">
                    <label for="social_vk">ВКонтакте:</label>
                    <input type="url" name="settings[social_vk]" id="social_vk" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['social_vk'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="social_telegram">Telegram:</label>
                    <input type="url" name="settings[social_telegram]" id="social_telegram" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['social_telegram'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="settings-section">
                <h3>Настройки бронирования</h3>
                
                <div class="form-group">
                    <label for="booking_enabled">Включить бронирование:</label>
                    <select name="settings[booking_enabled]" id="booking_enabled" class="form-control">
                        <option value="1" <?php echo (($settings['booking_enabled'] ?? '1') == '1') ? 'selected' : ''; ?>>Да</option>
                        <option value="0" <?php echo (($settings['booking_enabled'] ?? '1') == '0') ? 'selected' : ''; ?>>Нет</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Сохранить настройки
                </button>
            </div>
        </form>
    </div>
</div> 