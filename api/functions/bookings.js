// Функция Netlify для обработки запросов бронирования
const { MongoClient, ObjectId } = require('mongodb');
const jwt = require('jsonwebtoken');
const { Handler } = require('@netlify/functions');

// Константы для конфигурации
const JWT_SECRET = process.env.JWT_SECRET || 'lesnoy-dvorik-default-secret-key';
const MONGODB_URI = process.env.MONGODB_URI || 'mongodb://localhost:27017/lesnoy-dvorik';
const DB_NAME = 'lesnoy-dvorik';

// Типы номеров и их стоимость
const ROOM_PRICES = {
  'standard': 3500,
  'comfort': 5000,
  'luxe': 7500,
  'premium': 10000
};

// Емкость номеров (максимальное количество гостей)
const ROOM_CAPACITY = {
  'standard': 2,
  'comfort': 3,
  'luxe': 4,
  'premium': 5
};

// Подключение к MongoDB
let cachedDb = null;
async function connectToDatabase() {
  if (cachedDb) {
    return cachedDb;
  }
  
  const client = new MongoClient(MONGODB_URI, {
    useNewUrlParser: true,
    useUnifiedTopology: true
  });
  
  await client.connect();
  const db = client.db(DB_NAME);
  
  cachedDb = db;
  return db;
}

// Проверка наличия необходимых коллекций и создание базовой структуры
async function ensureDbStructure(db) {
  const collections = await db.listCollections().toArray();
  const collectionNames = collections.map(c => c.name);
  
  // Создаем коллекцию бронирований, если ее нет
  if (!collectionNames.includes('bookings')) {
    await db.createCollection('bookings');
  }
  
  // Создаем коллекцию номеров, если ее нет
  if (!collectionNames.includes('rooms')) {
    await db.createCollection('rooms');
    
    // Добавляем базовые номера, если коллекция пуста
    const rooms = await db.collection('rooms').countDocuments();
    if (rooms === 0) {
      const baseRooms = [
        {
          room_type: 'standard',
          price_per_night: ROOM_PRICES.standard,
          capacity: ROOM_CAPACITY.standard,
          description: 'Стандартный номер с одной двуспальной кроватью, телевизором и базовыми удобствами.',
          amenities: 'Wi-Fi, ТВ, холодильник, душ',
          status: 'available',
          room_number: '101'
        },
        {
          room_type: 'standard',
          price_per_night: ROOM_PRICES.standard,
          capacity: ROOM_CAPACITY.standard,
          description: 'Стандартный номер с одной двуспальной кроватью, телевизором и базовыми удобствами.',
          amenities: 'Wi-Fi, ТВ, холодильник, душ',
          status: 'available',
          room_number: '102'
        },
        {
          room_type: 'comfort',
          price_per_night: ROOM_PRICES.comfort,
          capacity: ROOM_CAPACITY.comfort,
          description: 'Комфортабельный номер с большой кроватью, зоной отдыха и улучшенной ванной комнатой.',
          amenities: 'Wi-Fi, ТВ, мини-бар, сейф, кондиционер, душевая кабина',
          status: 'available',
          room_number: '201'
        },
        {
          room_type: 'comfort',
          price_per_night: ROOM_PRICES.comfort,
          capacity: ROOM_CAPACITY.comfort,
          description: 'Комфортабельный номер с большой кроватью, зоной отдыха и улучшенной ванной комнатой.',
          amenities: 'Wi-Fi, ТВ, мини-бар, сейф, кондиционер, душевая кабина',
          status: 'available',
          room_number: '202'
        },
        {
          room_type: 'luxe',
          price_per_night: ROOM_PRICES.luxe,
          capacity: ROOM_CAPACITY.luxe,
          description: 'Просторный люкс с гостиной зоной, спальней и джакузи в ванной комнате.',
          amenities: 'Wi-Fi, ТВ, мини-бар, сейф, кондиционер, джакузи, халаты, тапочки',
          status: 'available',
          room_number: '301'
        },
        {
          room_type: 'premium',
          price_per_night: ROOM_PRICES.premium,
          capacity: ROOM_CAPACITY.premium,
          description: 'Премиум-люкс с панорамными окнами, отдельной гостиной, спальней и террасой.',
          amenities: 'Wi-Fi, ТВ, мини-бар, сейф, кондиционер, джакузи, халаты, тапочки, терраса, обслуживание в номере',
          status: 'available',
          room_number: '401'
        }
      ];
      
      await db.collection('rooms').insertMany(baseRooms);
    }
  }
}

// Проверка доступности номеров на указанные даты
async function checkAvailability(arrivalDate, departureDate) {
  const db = await connectToDatabase();
  
  // Преобразуем строки дат в объекты Date
  const arrival = new Date(arrivalDate);
  const departure = new Date(departureDate);
  
  // Получаем все бронирования, которые пересекаются с указанным периодом
  const bookings = await db.collection('bookings').find({
    $or: [
      // Начало бронирования внутри указанного периода
      {
        checkInDate: { $gte: arrival, $lt: departure }
      },
      // Конец бронирования внутри указанного периода
      {
        checkOutDate: { $gt: arrival, $lte: departure }
      },
      // Указанный период полностью внутри существующего бронирования
      {
        checkInDate: { $lte: arrival },
        checkOutDate: { $gte: departure }
      }
    ],
    status: { $in: ['pending', 'confirmed'] }
  }).toArray();
  
  // Получаем список номеров, которые заняты в указанный период
  const bookedRoomIds = bookings.map(booking => booking.roomId).filter(id => id);
  
  // Получаем все номера
  const allRooms = await db.collection('rooms').find().toArray();
  
  // Фильтруем доступные номера
  const availableRooms = allRooms.filter(room => 
    !bookedRoomIds.includes(room._id.toString()) && 
    room.status === 'available'
  );
  
  return availableRooms;
}

// Создание нового бронирования
async function createBooking(bookingData) {
  const db = await connectToDatabase();
  
  // Добавляем метки времени и статус
  const newBooking = {
    ...bookingData,
    created_at: new Date(),
    updated_at: new Date(),
    status: 'pending'
  };
  
  const result = await db.collection('bookings').insertOne(newBooking);
  
  return {
    success: true,
    data: {
      ...newBooking,
      _id: result.insertedId
    }
  };
}

// Получение бронирования по ID
async function getBookingById(id) {
  const db = await connectToDatabase();
  
  try {
    const booking = await db.collection('bookings').findOne({ _id: new ObjectId(id) });
    
    if (!booking) {
      return {
        success: false,
        message: 'Бронирование не найдено'
      };
    }
    
    return {
      success: true,
      data: booking
    };
  } catch (error) {
    return {
      success: false,
      message: 'Ошибка при получении бронирования',
      error: error.message
    };
  }
}

// Обновление статуса бронирования
async function updateBookingStatus(id, status) {
  const db = await connectToDatabase();
  
  try {
    const result = await db.collection('bookings').updateOne(
      { _id: new ObjectId(id) },
      { 
        $set: { 
          status: status,
          updated_at: new Date()
        } 
      }
    );
    
    if (result.matchedCount === 0) {
      return {
        success: false,
        message: 'Бронирование не найдено'
      };
    }
    
    return {
      success: true,
      message: 'Статус бронирования обновлен'
    };
  } catch (error) {
    return {
      success: false,
      message: 'Ошибка при обновлении статуса',
      error: error.message
    };
  }
}

// Валидация данных бронирования
function validateBookingData(data) {
  // Проверяем наличие обязательных полей
  const requiredFields = ['clientName', 'clientPhone', 'clientEmail', 'roomType', 'checkInDate', 'checkOutDate', 'guestsCount'];
  
  for (const field of requiredFields) {
    if (!data[field]) {
      return {
        valid: false,
        message: `Поле ${field} является обязательным`
      };
    }
  }
  
  // Проверяем формат даты заезда и выезда
  const checkInDate = new Date(data.checkInDate);
  const checkOutDate = new Date(data.checkOutDate);
  
  if (isNaN(checkInDate.getTime())) {
    return {
      valid: false,
      message: 'Некорректная дата заезда'
    };
  }
  
  if (isNaN(checkOutDate.getTime())) {
    return {
      valid: false,
      message: 'Некорректная дата выезда'
    };
  }
  
  // Проверяем, что дата выезда позже даты заезда
  if (checkInDate >= checkOutDate) {
    return {
      valid: false,
      message: 'Дата выезда должна быть позже даты заезда'
    };
  }
  
  // Проверяем, что дата заезда не в прошлом
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  if (checkInDate < today) {
    return {
      valid: false,
      message: 'Дата заезда не может быть в прошлом'
    };
  }
  
  // Проверяем тип номера
  if (!Object.keys(ROOM_PRICES).includes(data.roomType)) {
    return {
      valid: false,
      message: 'Указан неверный тип номера'
    };
  }
  
  // Проверяем количество гостей
  const maxGuests = ROOM_CAPACITY[data.roomType];
  
  if (parseInt(data.guestsCount) < 1 || parseInt(data.guestsCount) > maxGuests) {
    return {
      valid: false,
      message: `Для данного типа номера допустимо от 1 до ${maxGuests} гостей`
    };
  }
  
  return {
    valid: true
  };
}

// Проверка авторизации для админских запросов
const checkAuth = (event) => {
  const authHeader = event.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return null;
  }
  
  const token = authHeader.split(' ')[1];
  try {
    return jwt.verify(token, JWT_SECRET);
  } catch (error) {
    return null;
  }
};

// Обработчик GET запросов (получение свободных номеров, получение бронирования)
const handleGet = async (event) => {
  const path = event.path.replace('/.netlify/functions/bookings', '');
  const params = event.queryStringParameters || {};
  
  // Получение доступных номеров на указанные даты
  if (path === '/available') {
    try {
      if (!params.arrivalDate || !params.departureDate) {
        return {
          statusCode: 400,
          body: JSON.stringify({
            success: false,
            message: 'Необходимо указать даты заезда и выезда',
          }),
        };
      }
      
      const db = await connectToDatabase();
      
      // Проверка имеющихся бронирований на указанные даты
      const bookings = await db.collection('bookings').find({
        $or: [
          {
            arrival_date: { $lte: params.departureDate },
            departure_date: { $gte: params.arrivalDate },
          },
        ],
      }).toArray();
      
      // Получение всех номеров
      const allRooms = await db.collection('rooms').find({
        status: 'Доступен',
      }).toArray();
      
      // Фильтрация занятых номеров
      const bookedRoomNumbers = bookings.map(booking => booking.room_number);
      const availableRooms = allRooms.filter(room => !bookedRoomNumbers.includes(room.room_number));
      
      return {
        statusCode: 200,
        body: JSON.stringify({
          success: true,
          data: availableRooms,
        }),
      };
    } catch (error) {
      console.error('Ошибка при получении доступных номеров:', error);
      
      return {
        statusCode: 500,
        body: JSON.stringify({
          success: false,
          message: 'Ошибка сервера при получении доступных номеров',
        }),
      };
    }
  }
  
  // Получение деталей бронирования по ID
  if (path.startsWith('/details/') && path.length > 9) {
    try {
      const bookingId = path.slice(9);
      
      const db = await connectToDatabase();
      const booking = await db.collection('bookings').findOne({
        _id: new ObjectId(bookingId),
      });
      
      if (!booking) {
        return {
          statusCode: 404,
          body: JSON.stringify({
            success: false,
            message: 'Бронирование не найдено',
          }),
        };
      }
      
      return {
        statusCode: 200,
        body: JSON.stringify({
          success: true,
          data: booking,
        }),
      };
    } catch (error) {
      console.error('Ошибка при получении деталей бронирования:', error);
      
      return {
        statusCode: 500,
        body: JSON.stringify({
          success: false,
          message: 'Ошибка сервера при получении деталей бронирования',
        }),
      };
    }
  }
  
  // Получение списка всех бронирований (для админа)
  if (path === '/admin/list') {
    try {
      // Проверка авторизации
      const userData = checkAuth(event);
      if (!userData) {
        return {
          statusCode: 401,
          body: JSON.stringify({
            success: false,
            message: 'Требуется авторизация',
          }),
        };
      }
      
      const db = await connectToDatabase();
      
      // Параметры пагинации
      const page = parseInt(params.page) || 1;
      const limit = parseInt(params.limit) || 10;
      const skip = (page - 1) * limit;
      
      // Фильтры
      const filter = {};
      if (params.status) {
        filter.status = params.status;
      }
      if (params.search) {
        filter.$or = [
          { name: { $regex: params.search, $options: 'i' } },
          { phone: { $regex: params.search, $options: 'i' } },
          { email: { $regex: params.search, $options: 'i' } },
        ];
      }
      
      // Получение и подсчет бронирований
      const bookings = await db.collection('bookings')
        .find(filter)
        .sort({ created_at: -1 })
        .skip(skip)
        .limit(limit)
        .toArray();
      
      const total = await db.collection('bookings').countDocuments(filter);
      
      return {
        statusCode: 200,
        body: JSON.stringify({
          success: true,
          data: {
            bookings,
            pagination: {
              total,
              page,
              limit,
              totalPages: Math.ceil(total / limit),
            },
          },
        }),
      };
    } catch (error) {
      console.error('Ошибка при получении списка бронирований:', error);
      
      return {
        statusCode: 500,
        body: JSON.stringify({
          success: false,
          message: 'Ошибка сервера при получении списка бронирований',
        }),
      };
    }
  }
  
  // Для других GET запросов
  return {
    statusCode: 404,
    body: JSON.stringify({
      success: false,
      message: 'Ресурс не найден',
    }),
  };
};

// Обработчик POST запросов (создание бронирования)
const handlePost = async (event) => {
  try {
    const path = event.path.replace('/.netlify/functions/bookings', '');
    const body = JSON.parse(event.body);
    
    // Создание нового бронирования
    if (path === '/create') {
      // Валидация данных
      const validation = validateBookingData(body);
      
      if (!validation.valid) {
        return {
          statusCode: 400,
          body: JSON.stringify({
            success: false,
            message: validation.message
          })
        };
      }
      
      // Проверка доступности номера
      const availableRooms = await checkAvailability(body.checkInDate, body.checkOutDate);
      const isAvailable = availableRooms.some(room => room.room_type === body.roomType);
      
      if (!isAvailable) {
        return {
          statusCode: 400,
          body: JSON.stringify({
            success: false,
            message: 'Выбранный тип номера недоступен на указанные даты'
          })
        };
      }
      
      // Находим подходящий номер
      const selectedRoom = availableRooms.find(room => room.room_type === body.roomType);
      
      // Рассчитываем общую стоимость
      const checkInDate = new Date(body.checkInDate);
      const checkOutDate = new Date(body.checkOutDate);
      const nights = Math.floor((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
      const totalPrice = nights * ROOM_PRICES[body.roomType];
      
      // Дополняем данные бронирования
      const enrichedBookingData = {
        ...body,
        roomId: selectedRoom._id.toString(),
        roomNumber: selectedRoom.room_number,
        checkInDate: new Date(body.checkInDate),
        checkOutDate: new Date(body.checkOutDate),
        nights,
        pricePerNight: ROOM_PRICES[body.roomType],
        totalPrice
      };
      
      // Создаем бронирование
      const result = await createBooking(enrichedBookingData);
      
      return {
        statusCode: 201,
        body: JSON.stringify(result)
      };
    }
    
    // Обновление статуса бронирования (для админа)
    if (path === '/admin/update-status') {
      // Проверка авторизации
      const userData = checkAuth(event);
      if (!userData) {
        return {
          statusCode: 401,
          body: JSON.stringify({
            success: false,
            message: 'Требуется авторизация',
          }),
        };
      }
      
      if (!body.booking_id || !body.status) {
        return {
          statusCode: 400,
          body: JSON.stringify({
            success: false,
            message: 'Необходимо указать ID бронирования и новый статус',
          }),
        };
      }
      
      const db = await connectToDatabase();
      const result = await db.collection('bookings').updateOne(
        { _id: new ObjectId(body.booking_id) },
        { 
          $set: { 
            status: body.status,
            updated_at: new Date().toISOString(),
          }
        }
      );
      
      if (result.matchedCount === 0) {
        return {
          statusCode: 404,
          body: JSON.stringify({
            success: false,
            message: 'Бронирование не найдено',
          }),
        };
      }
      
      return {
        statusCode: 200,
        body: JSON.stringify({
          success: true,
          message: 'Статус бронирования успешно обновлен',
        }),
      };
    }
    
    // Для других POST запросов
    return {
      statusCode: 404,
      body: JSON.stringify({
        success: false,
        message: 'Ресурс не найден',
      }),
    };
  } catch (error) {
    console.error('Ошибка при обработке POST запроса:', error);
    
    return {
      statusCode: 500,
      body: JSON.stringify({
        success: false,
        message: 'Внутренняя ошибка сервера',
      }),
    };
  }
};

// Обработчик DELETE запросов (удаление бронирования)
const handleDelete = async (event) => {
  try {
    const path = event.path.replace('/.netlify/functions/bookings', '');
    
    // Удаление бронирования (для админа)
    if (path.startsWith('/admin/delete/') && path.length > 14) {
      // Проверка авторизации
      const userData = checkAuth(event);
      if (!userData) {
        return {
          statusCode: 401,
          body: JSON.stringify({
            success: false,
            message: 'Требуется авторизация',
          }),
        };
      }
      
      const bookingId = path.slice(14);
      
      const db = await connectToDatabase();
      const result = await db.collection('bookings').deleteOne({
        _id: new ObjectId(bookingId),
      });
      
      if (result.deletedCount === 0) {
        return {
          statusCode: 404,
          body: JSON.stringify({
            success: false,
            message: 'Бронирование не найдено',
          }),
        };
      }
      
      return {
        statusCode: 200,
        body: JSON.stringify({
          success: true,
          message: 'Бронирование успешно удалено',
        }),
      };
    }
    
    // Для других DELETE запросов
    return {
      statusCode: 404,
      body: JSON.stringify({
        success: false,
        message: 'Ресурс не найден',
      }),
    };
  } catch (error) {
    console.error('Ошибка при обработке DELETE запроса:', error);
    
    return {
      statusCode: 500,
      body: JSON.stringify({
        success: false,
        message: 'Внутренняя ошибка сервера',
      }),
    };
  }
};

// Обновленный экспорт функции с использованием современного формата Netlify Functions
exports.handler = async (event, context) => {
  const headers = {
    'Content-Type': 'application/json',
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'Content-Type, Authorization',
    'Access-Control-Allow-Methods': 'GET, POST, DELETE, OPTIONS',
  };
  
  // Обработка предварительных запросов CORS
  if (event.httpMethod === 'OPTIONS') {
    return {
      statusCode: 204,
      headers,
      body: '',
    };
  }
  
  let response;
  
  // Маршрутизация запросов в зависимости от метода
  switch (event.httpMethod) {
    case 'GET':
      response = await handleGet(event);
      break;
    case 'POST':
      response = await handlePost(event);
      break;
    case 'DELETE':
      response = await handleDelete(event);
      break;
    default:
      response = {
        statusCode: 405,
        body: JSON.stringify({
          success: false,
          message: 'Метод не поддерживается',
        }),
      };
  }
  
  return {
    ...response,
    headers: { ...headers, ...response.headers },
  };
}; 