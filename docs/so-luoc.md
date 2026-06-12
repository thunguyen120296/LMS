### Learning Management System (LMS)

Giống Udemy/Coursera nội bộ.

Ví dụ:

User đăng nhập
Admin tạo khóa học
Instructor upload bài học
Student học
Thi online
Chấm điểm
Cấp chứng chỉ
Notification
Thanh toán (mock)

Domain này cực kỳ hợp để học architecture.

### Tổng quan kiến trúc

                    +----------------+
                    |    Frontend    |
                    +-------+--------+
                            |
                            v
                    +----------------+
                    |  API Gateway   |
                    +-------+--------+
                            |
      ------------------------------------------------
      |         |          |        |       |         |
      v         v          v        v       v         v

 Auth    User    Course   Exam  Payment  Notification
Service Service Service Service Service   Service

      |                                     |
      |                                     |
      v                                     v

 Keycloak                          Kafka/RabbitMQ

      |
      v

 PostgreSQL