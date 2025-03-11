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

// Инициализация сайта при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    // Анимация шапки при прокрутке
    const header = document.querySelector('.header');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Мобильное меню
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainMenu = document.querySelector('.main-menu');
    
    if (mobileMenuToggle && mainMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mainMenu.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
        });
    }

    // Плавная прокрутка к якорям
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Закрываем мобильное меню при клике на ссылку
                if (mainMenu && mainMenu.classList.contains('active')) {
                    mainMenu.classList.remove('active');
                    if (mobileMenuToggle) {
                        mobileMenuToggle.classList.remove('active');
                    }
                }
                
                // Плавная прокрутка
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Учитываем высоту шапки
                    behavior: 'smooth'
                });
            }
        });
    });

    // Анимация элементов при прокрутке
    const animatedElements = document.querySelectorAll('.fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right');
    
    if (animatedElements.length > 0) {
        // Функция для проверки, виден ли элемент в области просмотра
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8 &&
                rect.bottom >= 0
            );
        }
        
        // Функция для анимации элементов при прокрутке
        function animateOnScroll() {
            animatedElements.forEach(element => {
                if (isElementInViewport(element) && !element.classList.contains('animated')) {
                    element.classList.add('animated');
                }
            });
        }
        
        // Запускаем анимацию при загрузке и прокрутке
        animateOnScroll();
        window.addEventListener('scroll', animateOnScroll);
    }

    // Инициализация галереи изображений (если есть)
    const galleryItems = document.querySelectorAll('.gallery-item');
    if (galleryItems.length > 0) {
        galleryItems.forEach(item => {
            item.addEventListener('click', function() {
                const imgSrc = this.querySelector('img').getAttribute('src');
                const imgAlt = this.querySelector('img').getAttribute('alt');
                
                // Создаем модальное окно для просмотра изображения
                const modal = document.createElement('div');
                modal.classList.add('gallery-modal');
                modal.innerHTML = `
                    <div class="gallery-modal-content">
                        <span class="gallery-modal-close">&times;</span>
                        <img src="${imgSrc}" alt="${imgAlt}">
                        <p>${imgAlt}</p>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Предотвращаем прокрутку страницы при открытом модальном окне
                document.body.style.overflow = 'hidden';
                
                // Закрытие модального окна
                modal.querySelector('.gallery-modal-close').addEventListener('click', function() {
                    document.body.removeChild(modal);
                    document.body.style.overflow = '';
                });
                
                // Закрытие по клику вне изображения
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        document.body.removeChild(modal);
                        document.body.style.overflow = '';
                    }
                });
            });
        });
    }

    // Валидация формы бронирования
    const bookingForm = document.querySelector('.booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = bookingForm.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Добавляем сообщение об ошибке, если его еще нет
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const errorMessage = document.createElement('div');
                        errorMessage.classList.add('error-message');
                        errorMessage.textContent = 'Это поле обязательно для заполнения';
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    }
                } else {
                    field.classList.remove('error');
                    
                    // Удаляем сообщение об ошибке, если оно есть
                    if (field.nextElementSibling && field.nextElementSibling.classList.contains('error-message')) {
                        field.parentNode.removeChild(field.nextElementSibling);
                    }
                }
            });
            
            // Проверка дат (дата выезда должна быть позже даты заезда)
            const arrivalDate = bookingForm.querySelector('[name="arrival_date"]');
            const departureDate = bookingForm.querySelector('[name="departure_date"]');
            
            if (arrivalDate && departureDate && arrivalDate.value && departureDate.value) {
                const arrival = new Date(arrivalDate.value);
                const departure = new Date(departureDate.value);
                
                if (departure <= arrival) {
                    isValid = false;
                    departureDate.classList.add('error');
                    
                    // Добавляем сообщение об ошибке для даты выезда
                    if (!departureDate.nextElementSibling || !departureDate.nextElementSibling.classList.contains('error-message')) {
                        const errorMessage = document.createElement('div');
                        errorMessage.classList.add('error-message');
                        errorMessage.textContent = 'Дата выезда должна быть позже даты заезда';
                        departureDate.parentNode.insertBefore(errorMessage, departureDate.nextSibling);
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Прокручиваем к первому полю с ошибкой
                const firstErrorField = bookingForm.querySelector('.error');
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                }
            }
        });
        
        // Удаляем класс ошибки при вводе в поле
        bookingForm.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('error');
                
                // Удаляем сообщение об ошибке, если оно есть
                if (this.nextElementSibling && this.nextElementSibling.classList.contains('error-message')) {
                    this.parentNode.removeChild(this.nextElementSibling);
                }
            });
        });
    }

    // Калькулятор стоимости бронирования
    const roomTypeSelect = document.querySelector('[name="room_type"]');
    const guestsInput = document.querySelector('[name="guests"]');
    const arrivalDateInput = document.querySelector('[name="arrival_date"]');
    const departureDateInput = document.querySelector('[name="departure_date"]');
    const serviceCheckboxes = document.querySelectorAll('[name^="service_"]');
    const totalPriceElement = document.querySelector('.booking-total-price');
    
    if (roomTypeSelect && guestsInput && arrivalDateInput && departureDateInput && totalPriceElement) {
        // Базовые цены на номера
        const roomPrices = {
            'standard': 3000,
            'comfort': 4500,
            'lux': 7000,
            'family': 8500
        };
        
        // Функция для расчета стоимости
        function calculatePrice() {
            // Получаем базовую стоимость номера
            const roomType = roomTypeSelect.value;
            let basePrice = roomPrices[roomType] || 0;
            
            // Учитываем количество гостей (доплата за каждого гостя свыше 2)
            const guests = parseInt(guestsInput.value) || 1;
            if (guests > 2) {
                basePrice += (guests - 2) * 1000;
            }
            
            // Рассчитываем количество дней
            let days = 1;
            if (arrivalDateInput.value && departureDateInput.value) {
                const arrival = new Date(arrivalDateInput.value);
                const departure = new Date(departureDateInput.value);
                
                if (departure > arrival) {
                    const timeDiff = departure.getTime() - arrival.getTime();
                    days = Math.ceil(timeDiff / (1000 * 3600 * 24));
                }
            }
            
            // Базовая стоимость за все дни
            let totalPrice = basePrice * days;
            
            // Добавляем стоимость дополнительных услуг
            serviceCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const serviceName = checkbox.name.replace('service_', '');
                    const servicePrice = additionalServicesPrices[serviceName] || 0;
                    
                    // Для завтрака и ужина умножаем на количество дней и гостей
                    if (serviceName === 'breakfast' || serviceName === 'dinner') {
                        totalPrice += servicePrice * days * guests;
                    } else {
                        // Для сауны и других услуг - просто добавляем стоимость
                        totalPrice += servicePrice;
                    }
                }
            });
            
            // Обновляем отображение стоимости
            totalPriceElement.textContent = totalPrice.toLocaleString() + ' ₽';
        }
        
        // Вызываем расчет при изменении любого параметра
        [roomTypeSelect, guestsInput, arrivalDateInput, departureDateInput, ...serviceCheckboxes].forEach(element => {
            element.addEventListener('change', calculatePrice);
        });
        
        // Инициализируем расчет при загрузке страницы
        calculatePrice();
    }

    // Модальное окно политики конфиденциальности
    const privacyModal = document.getElementById('privacy-policy-modal');
    const privacyLink = document.getElementById('privacy-policy-link');
    const privacyClose = document.querySelector('.privacy-modal-close');
    
    if (privacyModal && privacyLink && privacyClose) {
        // Открытие модального окна
        privacyLink.addEventListener('click', function(e) {
            e.preventDefault();
            privacyModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Предотвращаем прокрутку страницы
        });
        
        // Закрытие модального окна при клике на крестик
        privacyClose.addEventListener('click', function() {
            privacyModal.style.display = 'none';
            document.body.style.overflow = ''; // Возвращаем прокрутку страницы
        });
        
        // Закрытие модального окна при клике вне его содержимого
        window.addEventListener('click', function(e) {
            if (e.target === privacyModal) {
                privacyModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
        
        // Закрытие модального окна при нажатии клавиши Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && privacyModal.style.display === 'block') {
                privacyModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    }
}); 