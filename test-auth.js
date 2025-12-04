// Тестовый скрипт для проверки авторизации
const API_URL = 'http://localhost:80/api';

async function testAuth() {
    console.log('Testing authentication...');

    // Тест регистрации
    try {
        const registerResponse = await fetch(`${API_URL}/auth/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                name: 'Test User',
                email: 'test@example.com',
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
                    email: 'test@example.com',
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

                // Тест выхода
                const logoutResponse = await fetch(`${API_URL}/auth/logout`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${loginData.data.access_token}`
                    }
                });

                const logoutData = await logoutResponse.json();
                console.log('Logout response:', logoutData);
                console.log('Logout successful!');
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Запуск теста
testAuth();
