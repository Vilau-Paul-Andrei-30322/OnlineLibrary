CREATE VIEW ActiveBookReservations AS
SELECT br.reservation_id, br.user_id, st.name, b.title, b.author, br.reservation_time
FROM BookReservations br
INNER JOIN Students st ON br.user_id = st.user_id
INNER JOIN Books b ON br.book_id = b.book_id;
