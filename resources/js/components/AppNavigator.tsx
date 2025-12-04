import { useEffect } from 'react';
import { router } from '@inertiajs/react';
import { useUserStore } from '@/store/userStore';

interface AppNavigatorProps {
    children: React.ReactNode;
}

export default function AppNavigator({ children }: AppNavigatorProps) {
    const user = useUserStore((state) => state.user);

    useEffect(() => {
        // Слушаем события изменения URL
        const handleLocationChange = () => {
            // Здесь можно добавить логику для проверки авторизации
            // при переходе по маршрутам
        };

        // Добавляем обработчик событий
        window.addEventListener('popstate', handleLocationChange);

        return () => {
            // Удаляем обработчик при размонтировании
            window.removeEventListener('popstate', handleLocationChange);
        };
    }, [user]);

    return <>{children}</>;
}
