# SSO Service Platform — Learning Management System (LMS)

Nền tảng học trực tuyến theo mô hình **microservices + SSO**, lấy cảm hứng từ Udemy/Coursera. Dự án được thiết kế để mở rộng thành hệ sinh thái nhiều ứng dụng frontend (học viên, quản trị viên, …) dùng chung một hệ thống xác thực và phân quyền.

---

## Mục tiêu sản phẩm

| Giai đoạn | Phạm vi |
|-----------|---------|
| **Hiện tại** | Xây dựng **LMS cho học viên/giảng viên**: đăng ký, đăng nhập SSO, duyệt khóa học, dashboard cá nhân |
| **Tiếp theo** | **Admin portal** — quản trị khóa học, người dùng, nội dung, báo cáo |
| **Tương lai** | Nhiều **frontend micro-app** (LMS, Admin, …) kết nối cùng bộ **SSO services** backend |

### Luồng nghiệp vụ chính (domain)

```
Đăng ký / Đăng nhập (SSO)
    → Duyệt & tìm kiếm khóa học
    → Đăng ký khóa học (Enrollment)
    → Học bài (Lesson / Section)
    → Làm bài kiểm tra (Assessment)
    → Thanh toán (Payment — mock)
    → Nhận thông báo (Notification)
    → Cấp chứng chỉ (roadmap)
```

**Vai trò người dùng**

| Role | Mô tả |
|------|-------|
| `STUDENT` | Học viên — xem khóa học, học, làm bài |
| `INSTRUCTOR` | Giảng viên — tạo và quản lý khóa học |
| `ADMIN` | Quản trị viên — toàn quyền hệ thống (admin portal — roadmap) |

**Quyền (permissions) hiện có**

| Permission | Ý nghĩa |
|------------|---------|
| `COURSE:VIEW` | Xem danh sách / chi tiết khóa học |
| `COURSE:CREATE` | Tạo khóa học mới |

---

## Kiến trúc hệ thống

```
┌─────────────────────────────────────────────────────────────┐
│                     Frontend (micro-app)                    │
│  lms-frontend (React)  │  admin-frontend (roadmap)  │  …   │
└──────────────────────────────┬──────────────────────────────┘
                               │ HTTP
                               ▼
                    ┌──────────────────────┐
                    │   Nginx API Gateway   │  :8080
                    │   /api/*  /auth/*     │
                    └──────────┬───────────┘
                               │
     ┌─────────┬───────────┬───┴───┬───────────┬────────────┐
     ▼         ▼           ▼       ▼           ▼            ▼
 iam-service course-  enrollment payment  notification assessment
             service   -service  -service  -service    -service
     │         │           │       │           │            │
     └─────────┴───────────┴───────┴───────────┴────────────┘
                               │
              ┌────────────────┼────────────────┐
              ▼                ▼                ▼
         PostgreSQL         Keycloak      Redis / RabbitMQ
      (schema / service)   (SSO / OIDC)   (cache / events)
```

### Nguyên tắc thiết kế

- **Database-per-schema**: Mỗi service sở hữu schema riêng trên PostgreSQL (`iam`, `course`, `enrollment`, …).
- **Không FK cross-service**: Tham chiếu user giữa service qua UUID + gọi API (ví dụ `instructorId` trong Course).
- **SSO tập trung**: Keycloak quản lý identity, role, permission; các service xác thực JWT.
- **Shared library**: Logic dùng chung (`BaseController`, logging, exception, …) qua package `thunla/lms-shared-library`.
- **API Gateway**: Nginx route theo prefix `/api/{service}`.

---

## Tech stack

| Tầng | Công nghệ |
|------|-----------|
| **Backend** | PHP 8.4, Symfony 8.1, Doctrine ORM, Lexik JWT |
| **Frontend** | React 19, TypeScript, Vite, Tailwind CSS 4 |
| **State / Data** | Zustand, TanStack Query, React Hook Form, Zod |
| **Auth** | Keycloak (OpenID Connect), HttpOnly cookie |
| **Database** | PostgreSQL 15 |
| **Message broker** | RabbitMQ (sẵn sàng cho event-driven) |
| **Cache** | Redis |
| **DevOps** | Docker Compose, Nginx |

---

## Cấu trúc thư mục

```
sso-service/
├── docker-compose.yml          # Orchestration toàn bộ stack
├── infrastructure/
│   ├── nginx/                  # API Gateway config
│   ├── postgres/               # Init schema + data volume
│   ├── keycloak/import/        # Realm `lms` (roles, clients)
│   ├── jwt/                    # Keycloak public key (JWT verify)
│   └── scripts/                # sync-keycloak-public-key.sh
├── services/
│   ├── iam-service/            # Identity & Access Management
│   ├── course-service/         # Quản lý khóa học, bài học
│   ├── enrollment-service/     # Ghi danh khóa học
│   ├── payment-service/        # Thanh toán
│   ├── notification-service/   # Thông báo
│   └── assessment-service/     # Bài kiểm tra / chấm điểm
├── frontend/
│   └── lms-frontend/           # Ứng dụng web cho học viên
└── docs/
    └── so-luoc.md              # Ghi chú kiến trúc nội bộ
```

---

## Backend services

### `iam-service` — đã triển khai cơ bản

Trung tâm xác thực và đồng bộ user với Keycloak.

| Endpoint | Mô tả |
|----------|-------|
| `POST /api/iam/login` | Đăng nhập qua Keycloak, set HttpOnly cookie |
| `POST /api/iam/register` | Đăng ký user (Keycloak + DB local) |
| `POST /api/iam/logout` | Đăng xuất, xóa cookie |
| `POST /api/iam/refresh-token` | Làm mới access token |
| `GET /api/iam/me` | Thông tin user, roles, permissions |

Entity chính: `User`, `Company` (schema `iam`).

### `course-service` — đang phát triển

Domain model đã thiết kế: `Course`, `Section`, `Lesson`, `LessonResource`, `Tag`, `CourseTag`, `CourseRequirement`, `CourseLearningObjective`.

API hiện tại: health check + stub `POST /api/course/course/create` (yêu cầu `COURSE:CREATE`).

### Các service còn lại — scaffold

`enrollment-service`, `payment-service`, `notification-service`, `assessment-service` đã có cấu trúc Symfony, routing qua Nginx và schema DB riêng — sẵn sàng triển khai nghiệp vụ.

---

## Frontend — `lms-frontend`

Ứng dụng web dành cho **học viên và giảng viên** (learner-facing).

**Trang đã có**

| Route | Mô tả | Bảo vệ |
|-------|-------|--------|
| `/` | Trang chủ — hero, danh mục, khóa học nổi bật | Public |
| `/course-list` | Danh sách khóa học | Public |
| `/course-detail/:id` | Chi tiết khóa học | Public |
| `/login`, `/register`, `/forgot-password` | Auth | Guest only |
| `/dashboard` | Dashboard sau đăng nhập | Private |
| `/my-courses` | Khóa học của tôi | Private + `COURSE:VIEW` |
| `/profile` | Hồ sơ cá nhân | Private |
| `/forbidden` | Không đủ quyền | Private |

**Kiến trúc frontend**

- Feature-based: `features/auth`, `features/home`, …
- Route guard: `PublicRoute`, `PrivateRoute`, `GuestRoute`, `PermissionGate`
- Auth state: Zustand store + bootstrap session qua `/api/iam/me`
- Proxy dev: Vite `:3000` → Nginx gateway `:8080`

> **Lưu ý:** Dữ liệu khóa học trên trang chủ hiện dùng **mock data**; tích hợp API `course-service` đang trong roadmap.

---

## Xác thực & phân quyền (SSO flow)

```
1. User gửi credentials → iam-service
2. iam-service gọi Keycloak token endpoint (realm `lms`)
3. Keycloak trả access_token + refresh_token
4. iam-service set HttpOnly cookie → browser
5. Frontend gọi GET /api/iam/me → nhận user, roles, permissions
6. Các service khác verify JWT bằng Keycloak public key (infrastructure/jwt/)
7. Symfony Security + PermissionVoter kiểm tra quyền (vd. COURSE:CREATE)
```

Keycloak realm `lms` import sẵn roles (`STUDENT`, `INSTRUCTOR`, `ADMIN`) và client permissions.

---

## Chạy dự án (Development)

### Yêu cầu

- Docker & Docker Compose
- Node.js 20+ (cho frontend)
- Composer (cho backend, nếu chạy ngoài container)

### 1. Backend & infrastructure

```bash
# Tạo file .env ở root với các biến:
# POSTGRES_DB, POSTGRES_USER, POSTGRES_PASSWORD
# KEYCLOAK_ADMIN, KEYCLOAK_ADMIN_PASSWORD

docker compose up -d
```

| Service | URL |
|---------|-----|
| API Gateway | http://localhost:8080 |
| Keycloak Admin | http://localhost:8082 |
| PostgreSQL | localhost:5432 |
| Redis | localhost:6379 |
| RabbitMQ Management | http://localhost:15672 |

Sau khi Keycloak khởi động, đồng bộ public key để verify JWT:

```bash
bash infrastructure/scripts/sync-keycloak-public-key.sh
```

Chạy migration cho từng service (ví dụ IAM):

```bash
docker compose exec iam-service php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. Frontend

```bash
cd frontend/lms-frontend
npm install
npm run dev
```

Frontend chạy tại http://localhost:3000, proxy API qua gateway `:8080`.

---

## Trạng thái hiện tại & roadmap

### Đã hoàn thành

- [x] Kiến trúc microservices + Docker Compose
- [x] Nginx API Gateway với rate limiting
- [x] Keycloak SSO (realm, roles, permissions)
- [x] IAM: đăng ký, đăng nhập, logout, refresh token, `/me`
- [x] JWT authentication cho các backend service
- [x] Course domain model (entities, repositories)
- [x] LMS frontend: UI trang chủ, auth, routing, phân quyền client-side

### Đang / sắp làm

- [ ] API CRUD khóa học (course-service) + tích hợp frontend
- [ ] Enrollment, Payment, Assessment, Notification — nghiệp vụ thực
- [ ] Event-driven giữa các service qua RabbitMQ
- [ ] **Admin frontend** — portal quản trị riêng biệt
- [ ] Tách frontend thành **micro-frontend** (mỗi app một domain/subpath)
- [ ] Certificate, analytics, social login

---

## Quy ước phát triển

- **API prefix**: `/api/{service-name}/…` (vd. `/api/iam/login`, `/api/course/course`)
- **Response format**: `{ success, message, data, errors }` qua `BaseController`
- **Permission naming**: `{RESOURCE}:{ACTION}` (vd. `COURSE:VIEW`)
- **Migration**: Mỗi service quản lý migration riêng trong schema của mình
- **Không commit**: `.env`, `infrastructure/postgres/data`, JWT secrets

---

## Tài liệu liên quan

- [docs/so-luoc.md](docs/so-luoc.md) — ghi chú kiến trúc và domain nội bộ
- [frontend/lms-frontend/](frontend/lms-frontend/) — source LMS web app

---

## Liên hệ / đóng góp

Dự án đang trong giai đoạn xây dựng nền tảng. Khi thêm service hoặc frontend mới, giữ nguyên pattern SSO + schema isolation + API Gateway để đảm bảo khả năng mở rộng.
