# Hệ thống Hướng Nghiệp

Khung dự án gồm **Laravel API** (`backend`) và **Vue 3** (`frontend`). Trang công khai và CMS dùng chung một frontend; phân quyền theo `role` sau khi đăng nhập. CMS dùng **Element Plus**.

## Cấu trúc Frontend

```
frontend/src/
├── layouts/
│   ├── user/MainLayout.vue
│   └── admin/AdminLayout.vue      # Element Plus
├── views/
│   ├── user/                      # Trang người dùng
│   └── admin/                     # CMS (Element Plus)
├── api/
├── router/
└── stores/
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
- CMS: `/admin` (chỉ admin, UI Element Plus)
