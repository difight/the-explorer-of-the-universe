import { useEffect, useState } from 'react';
import { useUserStore } from '@/store/userStore';
import { router } from '@inertiajs/react';

interface ProtectedRouteProps {
    children: React.ReactNode;
}

export default function ProtectedRoute({ children }: ProtectedRouteProps) {
    const user = useUserStore((state) => state.user);
    const setUser = useUserStore((state) => state.setUser);
    const [isChecking, setIsChecking] = useState(true);

    useEffect(() => {
        const initializeAuth = async () => {
            // Проверяем, есть ли токен в localStorage
            const accessToken = localStorage.getItem('access_token');

            if (!user && accessToken) {
                // Если есть токен, но нет данных пользователя в store, получаем их
                try {
                    const response = await fetch(`${import.meta.env.VITE_APP_URL || "http://localhost:8000"}/api/auth/user`, {
                        method: 'GET',
                        headers: {
                            "Accept": "application/json",
                            "Authorization": `Bearer ${accessToken}`,
                        },
                        credentials: 'include',
                    });

                    if (response.ok) {
                        const userData = await response.json();
                        // Сохраняем данные пользователя в store
                        setUser({
                            access_token: localStorage.getItem('access_token') || '',
                            access_token_expires_at: localStorage.getItem('access_token_expires_at') || '',
                            refresh_token: localStorage.getItem('refresh_token') || '',
                            refresh_token_expires_at: localStorage.getItem('refresh_token_expires_at') || '',
                            user: userData.data.user
                        });
                    } else {
                        // Если не удалось получить данные пользователя, очищаем токены
                        localStorage.removeItem('access_token');
                        localStorage.removeItem('access_token_expires_at');
                        localStorage.removeItem('refresh_token');
                        localStorage.removeItem('refresh_token_expires_at');
                        router.visit('/');
                        return;
                    }
                } catch (error) {
                    console.error('Error fetching user data:', error);
                    router.visit('/');
                    return;
                }
            } else if (!user && !accessToken) {
                // Если нет ни токена, ни данных пользователя, перенаправляем на главную
                router.visit('/');
                return;
            }

            setIsChecking(false);
        };

        initializeAuth();
    }, [user, setUser]);

    // Show loading while checking auth status
    if (isChecking) {
        return <div>Loading...</div>;
    }

    // If user exists or we can't verify (but didn't get redirect), show children
    return <>{children}</>;
}
