// Тестовый скрипт для проверки авторизации во фронтенде
const API_URL = 'http://localhost/api';

async function testFrontendAuth() {
    console.log('Testing frontend auth...');

    try {
        // Тест регистрации
        const registerResponse = await fetch(`${API_URL}/auth/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name: 'Frontend Test User',
                email: 'frontend@example.com',
                password: 'password',
                password_confirmation: 'password'
            })
        });

        const registerData = await registerResponse.json();
        console.log('Register response:', registerData);

        if (registerData.data && registerData.data.access_token) {
            console.log('Registration successful!');

            // Тест входа
            const loginResponse = await fetch(`${API_URL}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    email: 'frontend@example.com',
                    password: 'password'
                })
            });

            const loginData = await loginResponse.json();
            console.log('Login response:', loginData);

            if (loginData.data && loginData.data.access_token) {
                console.log('Login successful!');

                // Тест получения данных пользователя
                const userResponse = await fetch(`${API_URL}/auth/user`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${loginData.data.access_token}`
                    }
                });

                const userData = await userResponse.json();
                console.log('User data:', userData);
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Запуск теста
testFrontendAuth();
