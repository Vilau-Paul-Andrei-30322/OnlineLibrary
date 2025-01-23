CREATE VIEW UserData AS
SELECT 
    user_id, 
    name, 
    email, 
    student_id, 
    has_seat_reservation, 
    is_admin, 
    has_book_reservations 
FROM 
    Students;
