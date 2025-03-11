// Стоимость дополнительных услуг
const additionalServicesPrices = {
    'breakfast': 500,
    'dinner': 800,
    'sauna': 2000
}; 

// Функция для обработки выбранных дополнительных услуг
function processAdditionalServices(selectedServices) {
    let additionalServicesText = '';
    if (selectedServices && selectedServices.length > 0) {
        selectedServices.forEach(service => {
            switch(service) {
                case 'breakfast': additionalServicesText += '- Завтрак<br>'; break;
                case 'dinner': additionalServicesText += '- Ужин<br>'; break;
                case 'sauna': additionalServicesText += '- Сауна<br>'; break;
            }
        });
    }
    return additionalServicesText;
}

// Функция для получения списка выбранных услуг
function getSelectedServices(form) {
    const additionalServices = [];
    const checkboxes = form.querySelectorAll('input[type="checkbox"]:checked');
    checkboxes.forEach(checkbox => {
        const service = checkbox.name;
        switch(service) {
            case 'breakfast': additionalServices.push('Завтрак'); break;
            case 'dinner': additionalServices.push('Ужин'); break;
            case 'sauna': additionalServices.push('Сауна'); break;
        }
    });
    return additionalServices;
} 