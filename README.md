# Smartsense Travel

The server behind the Smartsense mobile app for travellers in Romania: points of interest, day experiences, a map, Maya (the app's AI travel assistant) and a community of travellers. It also serves the admin panel and the public website with the legal pages.

Built with Laravel 13, Inertia 3 with React 19, Tailwind 4 and the Laravel AI SDK. The Flutter app lives in its own repository.

## Run it locally

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate   # then set the database, OPENAI_API_KEY and the OSRM addresses
php artisan migrate
php artisan db:seed                                # roles, legal documents, categories and themes
php artisan db:seed --class=ContentSeeder          # the places, destinations and experiences
php artisan serve --port=8005
```

Routes are measured with OSRM (`services.osrm` in `config/services.php`), one server for cars and one for walking.

## Demonstration community

`php artisan db:seed --class=DemoCommunitySeeder` adds demonstration travellers with posts, comments, likes, reviews and follows, so the app can be shown with a living feed. It never runs in production. Remove it before launch with `php artisan community:remove-demo`: invented reviews are not allowed on a live service.

## Tests

```bash
php artisan test --compact
vendor/bin/pint --dirty
```

## Photos

Every photo of a place is the team's own, in the public domain or released under CC0, so the app shows it without a credit. The admin panel accepts no other licence.
