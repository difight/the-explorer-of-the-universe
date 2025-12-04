import { Container, Box, Heading, Text, Card, CardBody, CardHeader, CardFooter, Button, Divider } from '@chakra-ui/react'
import Header from '@/components/ui/Header';
import ProtectedRoute from '@/components/ProtectedRoute';
import { useUserStore } from '@/store/userStore';
import { navigate } from '@/routes';

export default function Profile() {
    const user = useUserStore((state) => state.user);

    return (
        <ProtectedRoute>
            <Container maxW="100%" p="0">
                <Header />
                <Container maxW="container.lg" py={8}>
                    <Heading as="h1" size="xl" mb={6}>Профиль пользователя</Heading>
                    {user ? (
                        <Card>
                            <CardHeader>
                                <Heading size="md">Личная информация</Heading>
                            </CardHeader>
                            <CardBody>
                                <Text><strong>Имя:</strong> {user.user.name}</Text>
                                <Text><strong>Email:</strong> {user.user.email}</Text>
                                {user.user.created_at && (
                                    <>
                                        <Divider my={3} />
                                        <Text><strong>Зарегистрирован:</strong> {new Date(user.user.created_at).toLocaleDateString('ru-RU')}</Text>
                                    </>
                                )}
                            </CardBody>
                            <CardFooter>
                                <Button colorScheme="blue" onClick={() => navigate('/my')}>
                                    Назад в кабинет
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
