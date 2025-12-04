// Тестовый скрипт для проверки фронтенда
const FRONTEND_URL = 'http://localhost';

async function testFrontend() {
    console.log('Testing frontend...');

    try {
        // Тест главной страницы
        const homeResponse = await fetch(`${FRONTEND_URL}/`);
        console.log('Home page status:', homeResponse.status);
        console.log('Home page headers:', homeResponse.headers.get('content-type'));

        // Тест страницы личного кабинета (должна перенаправить на главную, если не авторизован)
        const myResponse = await fetch(`${FRONTEND_URL}/my`);
        console.log('My page status:', myResponse.status);
        console.log('My page headers:', myResponse.headers.get('content-type'));

        // Тест API авторизации
        const authResponse = await fetch(`${FRONTEND_URL}/api/auth/user`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        });
        console.log('Auth API status:', authResponse.status);
        console.log('Auth API headers:', authResponse.headers.get('content-type'));

    } catch (error) {
        console.error('Error:', error);
    }
}

// Запуск теста
testFrontend();
