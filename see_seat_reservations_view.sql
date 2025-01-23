CREATE VIEW AllSeatReservations AS
SELECT sr.reservation_id, sr.user_id, st.name, sr.seat_id, sr.reservation_time, sr.is_active
FROM SeatReservations sr
INNER JOIN Students st ON sr.user_id = st.user_id;
