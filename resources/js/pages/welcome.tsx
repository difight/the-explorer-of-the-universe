import { Container, Heading, Text, Box, Button } from '@chakra-ui/react'
import Header from '@/components/ui/Header';
import AuthTest from '@/components/AuthTest';

export default function Welcome() {
    return (
        <Container maxW="100%" p="0">
            <Header />
            <Container maxW="container.lg" py={8}>
                <Box textAlign="center" py={10}>
                    <Heading as="h1" size="2xl" mb={6}>
                        Добро пожаловать в Explorer of the Universe
                    </Heading>
                    <Text fontSize="xl" mb={8}>
                        Исследуйте Вселенную и открывайте новые планеты!
                    </Text>
                    <Button
                        colorScheme="teal"
                        size="lg"
                        onClick={() => router.visit('/my')}
                    >
                        Начать исследование
                    </Button>
                </Box>
            </Container>
            <AuthTest />
        </Container>
    );
}
