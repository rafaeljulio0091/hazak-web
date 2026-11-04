<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Login do Hazak Web

O login usa Firebase Authentication com sessão Laravel. No Inertia, `POST /login`
retorna um redirecionamento HTTP 302: `/dashboard` quando autentica, ou `/login`
com erros de validação quando a tentativa falha. A tela exibe uma mensagem
genérica para credenciais inválidas e uma mensagem de indisponibilidade para
falhas de comunicação ou configuração do Firebase. Contas desativadas não podem
entrar nem recuperar sessões existentes. Papéis ausentes ou desconhecidos usam
o perfil de menor privilégio (`user`); apenas `admin` concede acesso administrativo.

As credenciais enviadas pelo próprio navegador permanecem visíveis na aba
Network do DevTools, inclusive com HTTPS. HTTPS protege o tráfego entre cliente
e servidor. Não compartilhe capturas com senhas, cookies ou tokens. Senhas não
devem ser gravadas em logs, armazenamento do navegador, props do Inertia ou
sessões. O formulário limpa a senha ao concluir a tentativa. Os avisos de falha
de login registram apenas o tipo da exceção, sem a exceção original do SDK, que
pode conter requisições com credenciais.

Em produção, configure TLS no servidor/proxy e as variáveis:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.example
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

As rotas de autenticação redirecionam leituras HTTP para HTTPS e recusam envios
HTTP em produção. Isso não substitui TLS, redirecionamento HTTP para HTTPS no
servidor antes de servir a aplicação, ou configuração correta de proxies
confiáveis quando o TLS termina no proxy. Restrinja os proxies confiáveis aos
endereços da infraestrutura; sem isso, podem ocorrer ciclos de redirecionamento.
O ambiente local continua aceitando HTTP.

O projeto Firebase deve ter o provedor E-mail/Senha habilitado, e o usuário deve
existir no mesmo projeto configurado no backend. As chamadas ao Firebase têm
timeout de conexão de 3 segundos e total de 10 segundos por requisição ao SDK,
ajustáveis por `FIREBASE_CONNECT_TIMEOUT` e `FIREBASE_TIMEOUT` (mínimo de 1 segundo).
O login pode fazer mais de uma chamada ao SDK. O limite existente de cinco
falhas por e-mail/IP em um minuto foi mantido, com a chave do cache em hash.
Em múltiplas instâncias, use um cache compartilhado para o limite valer em todos
os servidores.

Após alterar configuração de ambiente, atualize o cache de configuração:

```bash
php artisan config:clear
# Em produção, após conferir a configuração:
php artisan config:cache
```

Esta revisão não adiciona migrações. A tabela `sessions.user_id` precisa aceitar
UIDs de texto do Firebase; aplique a migração existente
`2026_10_01_000000_change_session_user_id_to_string.php` nos ambientes pendentes.

Validação local:

```bash
php artisan test
npm run build
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
