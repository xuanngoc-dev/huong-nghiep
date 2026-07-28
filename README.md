# Hệ thống Hướng Nghiệp

Khung dự án gồm **Laravel API** (`backend`) và **Vue 3** (`frontend`). Trang công khai và CMS dùng chung một frontend; phân quyền theo `role` sau khi đăng nhập.

## Cấu trúc

```
huong-nghiep/
├── backend/     # Laravel 13 + Sanctum
└── frontend/    # Vue 3 (site + CMS /admin)
```

## Vai trò

| Role | Quyền |
|------|--------|
| `user` | Trang công khai, hồ sơ, nộp trắc nghiệm |
| `admin` | Toàn quyền user + CMS `/admin` + API `/api/v1/admin/*` |

Tài khoản seed:

- Admin: `admin@huongnghiep.local` / `password`
- User: `user@huongnghiep.local` / `password`

## Chạy Backend

```bash
cd backend
composer install
php artisan migrate
php artisan db:seed
php artisan serve      # http://localhost:8000
```

## Chạy Frontend

```bash
cd frontend
npm install
npm run dev           # http://localhost:5173
```

- Site: `/`
- CMS: `/admin` (chỉ admin)

## Luồng đăng nhập

1. FE gọi `POST /api/v1/auth/login`
2. BE trả `user` (có `role`) + `token`
3. Nếu `role === admin` → chuyển vào `/admin`
4. Nếu `role === user` → về trang chủ
5. Route guard chặn `/admin` nếu không phải admin
6. API admin dùng middleware `auth:sanctum` + `role:admin`
