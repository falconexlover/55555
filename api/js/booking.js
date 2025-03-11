/**
 * Скрипт для обработки бронирований в гостинице "Лесной дворик"
 */

document.addEventListener('DOMContentLoaded', function() {
  // Инициализация элементов формы бронирования
  const bookingForm = document.getElementById('booking-form');
  const roomTypeSelect = document.getElementById('room-type');
  const arrivalDateInput = document.getElementById('arrival-date');
  const departureDateInput = document.getElementById('departure-date');
  const guestsInput = document.getElementById('guests');
  const priceDisplay = document.getElementById('price-display');
  const totalPriceDisplay = document.getElementById('total-price');
  const roomTypeError = document.getElementById('room-type-error');
  const dateError = document.getElementById('date-error');
  const checkAvailabilityBtn = document.getElementById('check-availability');
  const bookingSummary = document.getElementById('booking-summary');
  
  // Минимальная дата заезда - сегодня
  const today = new Date();
  const todayFormatted = formatDateForInput(today);
  arrivalDateInput.min = todayFormatted;
  
  // Минимальная дата выезда - завтра
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const tomorrowFormatted = formatDateForInput(tomorrow);
  departureDateInput.min = tomorrowFormatted;
  
  // Обработчики событий
  if (arrivalDateInput) {
    arrivalDateInput.addEventListener('change', function() {
      // Устанавливаем минимальную дату выезда - день после заезда
      const selectedDate = new Date(this.value);
      const nextDay = new Date(selectedDate);
      nextDay.setDate(nextDay.getDate() + 1);
      departureDateInput.min = formatDateForInput(nextDay);
      
      // Если текущая дата выезда раньше минимальной, корректируем
      if (new Date(departureDateInput.value) <= selectedDate) {
        departureDateInput.value = formatDateForInput(nextDay);
      }
      
      // Пересчитываем стоимость, если выбран тип номера
      if (roomTypeSelect.value) {
        calculatePrice();
      }
    });
  }
  
  if (departureDateInput) {
    departureDateInput.addEventListener('change', function() {
      // Пересчитываем стоимость, если выбран тип номера
      if (roomTypeSelect.value) {
        calculatePrice();
      }
    });
  }
  
  if (roomTypeSelect) {
    roomTypeSelect.addEventListener('change', function() {
      // Пересчитываем стоимость
      calculatePrice();
    });
  }
  
  if (guestsInput) {
    guestsInput.addEventListener('change', function() {
      validateGuests();
    });
  }
  
  // Обработчик для кнопки проверки доступности
  if (checkAvailabilityBtn) {
    checkAvailabilityBtn.addEventListener('click', function(event) {
      event.preventDefault();
      checkAvailability();
    });
  }
  
  // Обработчик отправки формы бронирования
  if (bookingForm) {
    bookingForm.addEventListener('submit', function(event) {
      event.preventDefault();
      
      // Валидация формы перед отправкой
      if (!validateBookingForm()) {
        return;
      }
      
      // Отправка данных на сервер
      submitBooking();
    });
  }
  
  // Функция проверки доступности номеров
  function checkAvailability() {
    if (!validateDates()) {
      return;
    }
    
    const arrivalDate = arrivalDateInput.value;
    const departureDate = departureDateInput.value;
    
    // Отображаем индикатор загрузки
    const loadingElement = document.getElementById('loading-indicator');
    if (loadingElement) {
      loadingElement.style.display = 'block';
    }
    
    // Запрос на сервер для проверки доступных номеров
    fetch(`/.netlify/functions/bookings/available?arrivalDate=${arrivalDate}&departureDate=${departureDate}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('Ошибка при получении данных');
        }
        return response.json();
      })
      .then(data => {
        if (loadingElement) {
          loadingElement.style.display = 'none';
        }
        
        // Обновляем список доступных номеров
        updateAvailableRooms(data.data);
      })
      .catch(error => {
        console.error('Ошибка:', error);
        if (loadingElement) {
          loadingElement.style.display = 'none';
        }
        
        // Показываем сообщение об ошибке
        showError('Произошла ошибка при проверке доступности номеров. Пожалуйста, попробуйте позже.');
      });
  }
  
  // Функция обновления списка доступных номеров
  function updateAvailableRooms(rooms) {
    const roomsContainer = document.getElementById('available-rooms');
    
    if (!roomsContainer) {
      return;
    }
    
    roomsContainer.innerHTML = '';
    
    if (!rooms || rooms.length === 0) {
      roomsContainer.innerHTML = '<p class="no-rooms">К сожалению, на выбранные даты нет свободных номеров.</p>';
      return;
    }
    
    const roomsHeading = document.createElement('h3');
    roomsHeading.textContent = 'Доступные номера:';
    roomsContainer.appendChild(roomsHeading);
    
    // Группируем номера по типу
    const roomTypes = {};
    rooms.forEach(room => {
      if (!roomTypes[room.room_type]) {
        roomTypes[room.room_type] = {
          type: room.room_type,
          price: room.price_per_night,
          capacity: room.capacity,
          description: room.description,
          amenities: room.amenities,
          count: 0
        };
      }
      roomTypes[room.room_type].count += 1;
    });
    
    // Создаем карточки для каждого типа номера
    for (const type in roomTypes) {
      const room = roomTypes[type];
      
      const roomCard = document.createElement('div');
      roomCard.className = 'room-card';
      
      roomCard.innerHTML = `
        <div class="room-card-header">
          <h4>${room.type}</h4>
          <div class="room-price">${formatPrice(room.price)} ₽ / ночь</div>
        </div>
        <div class="room-card-content">
          <div class="room-details">
            <div class="room-capacity">
              <i class="fas fa-user"></i> До ${room.capacity} гостей
            </div>
            <div class="room-count">
              <i class="fas fa-door-open"></i> Доступно: ${room.count}
            </div>
          </div>
          <p class="room-description">${room.description || 'Описание недоступно'}</p>
          <div class="room-amenities">
            <strong>Удобства:</strong> ${room.amenities || 'Информация недоступна'}
          </div>
          <button class="btn btn-primary select-room-btn" data-room-type="${room.type}" data-price="${room.price}" data-capacity="${room.capacity}">
            Выбрать
          </button>
        </div>
      `;
      
      roomsContainer.appendChild(roomCard);
    }
    
    // Добавляем обработчики для кнопок выбора номера
    const selectButtons = document.querySelectorAll('.select-room-btn');
    selectButtons.forEach(button => {
      button.addEventListener('click', function() {
        const roomType = this.getAttribute('data-room-type');
        const price = this.getAttribute('data-price');
        const capacity = this.getAttribute('data-capacity');
        
        // Устанавливаем выбранный тип номера в форме
        if (roomTypeSelect) {
          roomTypeSelect.value = roomType;
        }
        
        // Устанавливаем максимальное количество гостей
        if (guestsInput) {
          guestsInput.max = capacity;
          if (parseInt(guestsInput.value) > parseInt(capacity)) {
            guestsInput.value = capacity;
          }
        }
        
        // Пересчитываем стоимость
        calculatePrice();
        
        // Прокручиваем к форме бронирования
        if (bookingForm) {
          bookingForm.scrollIntoView({ behavior: 'smooth' });
        }
      });
    });
    
    // Показываем контейнер с номерами
    roomsContainer.style.display = 'block';
  }
  
  // Функция пересчета стоимости
  function calculatePrice() {
    if (!roomTypeSelect || !arrivalDateInput || !departureDateInput || !totalPriceDisplay) {
      return;
    }
    
    const roomType = roomTypeSelect.value;
    const arrivalDate = new Date(arrivalDateInput.value);
    const departureDate = new Date(departureDateInput.value);
    
    if (!roomType || !arrivalDate || !departureDate || isNaN(arrivalDate) || isNaN(departureDate)) {
      totalPriceDisplay.textContent = '0 ₽';
      return;
    }
    
    // Находим цену за ночь для выбранного типа номера
    let pricePerNight = 0;
    const roomTypeOption = Array.from(roomTypeSelect.options).find(option => option.value === roomType);
    
    if (roomTypeOption) {
      pricePerNight = parseFloat(roomTypeOption.getAttribute('data-price')) || 0;
    }
    
    // Рассчитываем количество дней
    const timeDiff = departureDate - arrivalDate;
    const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
    
    // Рассчитываем общую стоимость
    const totalPrice = pricePerNight * nights;
    
    // Обновляем отображение
    if (priceDisplay) {
      priceDisplay.textContent = formatPrice(pricePerNight) + ' ₽';
    }
    
    totalPriceDisplay.textContent = formatPrice(totalPrice) + ' ₽';
    
    // Обновляем данные бронирования для отображения в итоге
    updateBookingSummary(roomType, arrivalDate, departureDate, nights, totalPrice);
  }
  
  // Функция обновления сводки бронирования
  function updateBookingSummary(roomType, arrivalDate, departureDate, nights, totalPrice) {
    if (!bookingSummary) {
      return;
    }
    
    bookingSummary.innerHTML = `
      <h3>Детали бронирования:</h3>
      <div class="booking-detail">
        <div class="detail-label">Тип номера:</div>
        <div class="detail-value">${roomType}</div>
      </div>
      <div class="booking-detail">
        <div class="detail-label">Дата заезда:</div>
        <div class="detail-value">${formatDateForDisplay(arrivalDate)}</div>
      </div>
      <div class="booking-detail">
        <div class="detail-label">Дата выезда:</div>
        <div class="detail-value">${formatDateForDisplay(departureDate)}</div>
      </div>
      <div class="booking-detail">
        <div class="detail-label">Количество ночей:</div>
        <div class="detail-value">${nights}</div>
      </div>
      <div class="booking-detail total">
        <div class="detail-label">Итого:</div>
        <div class="detail-value">${formatPrice(totalPrice)} ₽</div>
      </div>
    `;
    
    bookingSummary.style.display = 'block';
  }
  
  // Функция валидации формы бронирования
  function validateBookingForm() {
    let isValid = true;
    
    // Проверка выбора типа номера
    if (!roomTypeSelect.value) {
      if (roomTypeError) {
        roomTypeError.textContent = 'Пожалуйста, выберите тип номера';
        roomTypeError.style.display = 'block';
      }
      isValid = false;
    } else if (roomTypeError) {
      roomTypeError.style.display = 'none';
    }
    
    // Проверка дат
    if (!validateDates()) {
      isValid = false;
    }
    
    // Проверка количества гостей
    if (!validateGuests()) {
      isValid = false;
    }
    
    // Проверка личных данных
    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    const nameError = document.getElementById('name-error');
    const phoneError = document.getElementById('phone-error');
    
    if (nameInput && !nameInput.value.trim()) {
      if (nameError) {
        nameError.textContent = 'Пожалуйста, введите ваше имя';
        nameError.style.display = 'block';
      }
      isValid = false;
    } else if (nameError) {
      nameError.style.display = 'none';
    }
    
    if (phoneInput && !phoneInput.value.trim()) {
      if (phoneError) {
        phoneError.textContent = 'Пожалуйста, введите номер телефона';
        phoneError.style.display = 'block';
      }
      isValid = false;
    } else if (phoneError) {
      phoneError.style.display = 'none';
    }
    
    return isValid;
  }
  
  // Функция валидации дат
  function validateDates() {
    if (!arrivalDateInput || !departureDateInput || !dateError) {
      return true;
    }
    
    const arrivalDate = new Date(arrivalDateInput.value);
    const departureDate = new Date(departureDateInput.value);
    
    if (!arrivalDateInput.value) {
      dateError.textContent = 'Пожалуйста, выберите дату заезда';
      dateError.style.display = 'block';
      return false;
    }
    
    if (!departureDateInput.value) {
      dateError.textContent = 'Пожалуйста, выберите дату выезда';
      dateError.style.display = 'block';
      return false;
    }
    
    if (arrivalDate >= departureDate) {
      dateError.textContent = 'Дата выезда должна быть позже даты заезда';
      dateError.style.display = 'block';
      return false;
    }
    
    dateError.style.display = 'none';
    return true;
  }
  
  // Функция валидации количества гостей
  function validateGuests() {
    if (!guestsInput) {
      return true;
    }
    
    const guestsError = document.getElementById('guests-error');
    const guests = parseInt(guestsInput.value);
    const maxGuests = parseInt(guestsInput.max);
    
    if (isNaN(guests) || guests < 1) {
      if (guestsError) {
        guestsError.textContent = 'Пожалуйста, укажите количество гостей';
        guestsError.style.display = 'block';
      }
      return false;
    }
    
    if (guests > maxGuests) {
      if (guestsError) {
        guestsError.textContent = `Максимальное количество гостей для этого номера: ${maxGuests}`;
        guestsError.style.display = 'block';
      }
      return false;
    }
    
    if (guestsError) {
      guestsError.style.display = 'none';
    }
    
    return true;
  }
  
  // Функция отправки бронирования
  function submitBooking() {
    const name = document.getElementById('name').value;
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email')?.value || '';
    const roomType = roomTypeSelect.value;
    const arrivalDate = arrivalDateInput.value;
    const departureDate = departureDateInput.value;
    const guests = guestsInput.value;
    const comments = document.getElementById('comments')?.value || '';
    const paymentMethod = document.getElementById('payment-method')?.value || 'Не указан';
    const promoCode = document.getElementById('promo-code')?.value || '';
    
    // Подготавливаем данные для отправки
    const bookingData = {
      name,
      phone,
      email,
      room_type: roomType,
      arrival_date: arrivalDate,
      departure_date: departureDate,
      guests: parseInt(guests),
      comments,
      payment_method: paymentMethod,
      promo_code: promoCode
    };
    
    // Отображаем индикатор загрузки
    const bookingSubmitBtn = document.getElementById('booking-submit');
    const loadingIndicator = document.getElementById('submit-loading');
    
    if (bookingSubmitBtn) {
      bookingSubmitBtn.disabled = true;
      bookingSubmitBtn.textContent = 'Обработка...';
    }
    if (loadingIndicator) {
      loadingIndicator.style.display = 'inline-block';
    }
    
    // Отправляем запрос на сервер
    fetch('/.netlify/functions/bookings/create', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(bookingData)
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || 'Ошибка при бронировании');
        });
      }
      return response.json();
    })
    .then(data => {
      // Скрываем индикатор загрузки
      if (bookingSubmitBtn) {
        bookingSubmitBtn.disabled = false;
        bookingSubmitBtn.textContent = 'Забронировать';
      }
      if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
      }
      
      // Показываем сообщение об успешном бронировании
      showSuccess(data.message || 'Бронирование успешно создано!', data.data.booking_id);
      
      // Очищаем форму
      bookingForm.reset();
      
      // Обновляем отображение
      if (totalPriceDisplay) {
        totalPriceDisplay.textContent = '0 ₽';
      }
      if (bookingSummary) {
        bookingSummary.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Ошибка:', error);
      
      // Скрываем индикатор загрузки
      if (bookingSubmitBtn) {
        bookingSubmitBtn.disabled = false;
        bookingSubmitBtn.textContent = 'Забронировать';
      }
      if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
      }
      
      // Показываем сообщение об ошибке
      showError(error.message || 'Произошла ошибка при бронировании. Пожалуйста, попробуйте позже.');
    });
  }
  
  // Функция отображения сообщения об успешном бронировании
  function showSuccess(message, bookingId) {
    const successMessage = document.getElementById('success-message');
    const bookingDetails = document.getElementById('booking-details');
    
    if (successMessage) {
      successMessage.textContent = message;
      successMessage.style.display = 'block';
    }
    
    if (bookingDetails) {
      bookingDetails.innerHTML = `
        <p>Номер вашего бронирования: <strong>${bookingId}</strong></p>
        <p>Сохраните этот номер для дальнейших обращений.</p>
        <p>Мы отправили детали бронирования на указанный вами email (если указан).</p>
      `;
      bookingDetails.style.display = 'block';
    }
    
    // Скрываем форму бронирования
    if (bookingForm) {
      bookingForm.style.display = 'none';
    }
    
    // Прокручиваем к сообщению об успехе
    if (successMessage) {
      successMessage.scrollIntoView({ behavior: 'smooth' });
    }
  }
  
  // Функция отображения сообщения об ошибке
  function showError(message) {
    const errorMessage = document.getElementById('error-message');
    
    if (errorMessage) {
      errorMessage.textContent = message;
      errorMessage.style.display = 'block';
      
      // Скрываем сообщение через 5 секунд
      setTimeout(() => {
        errorMessage.style.display = 'none';
      }, 5000);
    }
  }
  
  // Вспомогательные функции
  
  // Форматирование даты для input type="date"
  function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
  
  // Форматирование даты для отображения
  function formatDateForDisplay(date) {
    return date.toLocaleDateString('ru-RU', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  }
  
  // Форматирование цены
  function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price);
  }
});

/**
 * Инициализация маски для телефона
 */
function initPhoneMask() {
  const phoneInput = document.getElementById('phone');
  
  if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
      let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,4})/);
      if (!x[1] && !x[2]) {
        e.target.value = '';
        return;
      }
      e.target.value = '+' + x[1] + (x[2] ? ' (' + x[2] + ')' : '') + (x[3] ? ' ' + x[3] : '') + (x[4] ? '-' + x[4] : '');
    });
  }
}

// Инициализация маски телефона при загрузке страницы
document.addEventListener('DOMContentLoaded', initPhoneMask); 