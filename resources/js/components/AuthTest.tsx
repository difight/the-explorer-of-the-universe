import { useState } from 'react';
import { useUserStore } from '@/store/userStore';
import WayToBackend from '@/lib/WayToBackend';

export default function AuthTest() {
    const user = useUserStore((state) => state.user);
    const setUser = useUserStore((state) => state.setUser);
    const clearUser = useUserStore((state) => state.clearUser);

    const [email, setEmail] = useState('test@example.com');
    const [password, setPassword] = useState('password');
    const [name, setName] = useState('Test User');

    const handleLogin = async () => {
        try {
            const result = await WayToBackend.loginUser({
                email,
                password
            });
            setUser(result.data.data);
            console.log('Login successful', result.data);
        } catch (error) {
            console.error('Login failed', error);
        }
    };

    const handleRegister = async () => {
        try {
            const result = await WayToBackend.registerUser({
                name,
                email,
                password,
                password_confirmation: password
            });
            setUser(result.data.data);
            console.log('Registration successful', result.data);
        } catch (error) {
            console.error('Registration failed', error);
        }
    };

    const handleLogout = async () => {
        try {
            await WayToBackend.logoutUser();
            clearUser();
            console.log('Logout successful');
        } catch (error) {
            console.error('Logout failed', error);
        }
    };

    return (
        <div style={{ padding: '20px', border: '1px solid #ccc', margin: '20px' }}>
            <h2>Auth Test Component</h2>

            {user ? (
                <div>
                    <p>Logged in as: {user.user.name} ({user.user.email})</p>
                    <button onClick={handleLogout}>Logout</button>
                </div>
            ) : (
                <div>
                    <p>Not logged in</p>
                    <div>
                        <input
                            type="text"
                            placeholder="Name"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                        />
                    </div>
                    <div>
                        <input
                            type="email"
                            placeholder="Email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                        />
                    </div>
                    <div>
                        <input
                            type="password"
                            placeholder="Password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                        />
                    </div>
                    <button onClick={handleRegister}>Register</button>
                    <button onClick={handleLogin}>Login</button>
                </div>
            )}
        </div>
    );
}
