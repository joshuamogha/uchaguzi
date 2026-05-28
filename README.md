# Church Digital Election System

A Laravel 13 church election application built with Blade views, Bootstrap 5, MySQL-friendly migrations, Eloquent relationships, policy-backed admin access, and service-layer voting logic.

## Stack

- Laravel 13
- Laravel Blade
- Bootstrap 5
- MySQL
- Session-based Laravel authentication using the default `users` table

## Main Features

- Admin dashboard with election, voter, and turnout summaries
- CRUD for communities, church groups, members, elections, contests, and candidates
- Voter generation from active members with secure token hashing and optional PIN hashing
- QR-ready voter cards using token-based vote links
- Secret ballot storage that never links ballots back to voters
- Contest-aware validation with different required selection counts
- Transactional vote submission with `lockForUpdate()` protection
- Public candidate pages
- Public results pages once an election is closed
- Admin CSV result export
- Election audit logging for verification and ballot events

## Setup

1. Install dependencies:

```bash
composer install
```

2. Configure your `.env` file for MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uchaguzi
DB_USERNAME=root
DB_PASSWORD=
```

3. Generate the application key if needed:

```bash
php artisan key:generate
```

4. Run migrations and seed sample data:

```bash
php artisan migrate --seed
```

5. Create the public storage symlink for candidate photos:

```bash
php artisan storage:link
```

6. Start the application:

```bash
php artisan serve
```

## Seeded Access

- Admin login:
  - Email: `admin@church.test`
  - Password: `password`

## Seeded Sample Data

- 18 communities
- Church groups for Elders, Choir, Youth, Women, and Men
- One active elders election with community contests and varying required selections
- One closed choir leadership election with position-based contests
- Sample candidates with placeholder images
- Seeded voters with generated tokens and PINs

## Voting Flow

1. Generate voter credentials from the admin voters screen.
2. Open or scan the generated link in the format `/vote/verify?token=PLAIN_TOKEN`.
3. Confirm the PIN if the voter has one.
4. Complete the ballot and review selections.
5. Submit the vote. Ballot creation and voter updates run in a single database transaction.

## Notes

- Candidate uploads are stored on `storage/app/public/candidates`.
- Ballots store only election and candidate selections, not voter identity.
- Result export is implemented as CSV without extra packages.
- QR rendering on voter cards is handled in the browser using a CDN script.

## Verification

```bash
php artisan route:list
php artisan test
```
