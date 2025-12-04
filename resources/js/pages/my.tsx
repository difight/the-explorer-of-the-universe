import { Container, Box, Heading, Text, Card, CardBody, CardHeader, CardFooter, Button } from '@chakra-ui/react'
import Header from '@/components/ui/Header';
import ProtectedRoute from '@/components/ProtectedRoute';
import { useUserStore } from '@/store/userStore';
import { router } from '@inertiajs/react';

export default function My() {
    const user = useUserStore((state) => state.user);

    return (
        <ProtectedRoute>
            <Container maxW="100%" p="0">
                <Header />
                <Container maxW="container.lg" py={8}>
                    <Heading as="h1" size="xl" mb={6}>Мой профиль</Heading>
                    {user ? (
                        <Card>
                            <CardHeader>
                                <Heading size="md">Информация о пользователе</Heading>
                            </CardHeader>
                            <CardBody>
                                <Text><strong>Имя:</strong> {user.user.name}</Text>
                                <Text><strong>Email:</strong> {user.user.email}</Text>
                                {user.user.created_at && (
                                    <Text><strong>Зарегистрирован:</strong> {new Date(user.user.created_at).toLocaleDateString('ru-RU')}</Text>
                                )}
                            </CardBody>
                            <CardFooter>
                                <Button colorScheme="blue" onClick={() => router.visit('/')}>
                                    Вернуться на главную
                                </Button>
                            </CardFooter>
                        </Card>
                    ) : (
                        <Box>
                            <Text>Загрузка информации о пользователе...</Text>
                        </Box>
                    )}
                </Container>
            </Container>
        </ProtectedRoute>
    );
}
