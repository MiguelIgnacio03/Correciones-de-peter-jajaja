-- Insertar más datos de ejemplo para testing
INSERT INTO books (title, isbn, author_id, publication_year, genre, publisher, total_copies, available_copies, description) VALUES
('El amor en los tiempos del cólera', '978-0307389732', 1, 1985, 'Novela', 'Editorial Sudamericana', 3, 3, 'Una historia de amor que atraviesa décadas'),
('Eva Luna', '978-1501169961', 2, 1987, 'Novela', 'Plaza & Janés', 4, 4, 'Las aventuras de una joven contadora de historias'),
('La fiesta del chivo', '978-8432217063', 3, 2000, 'Novela histórica', 'Alfaguara', 2, 2, 'Una novela sobre la dictadura de Trujillo'),
('Crónica de una muerte anunciada', '978-1400034955', 1, 1981, 'Novela', 'Editorial La Oveja Negra', 3, 3, 'La reconstrucción de un asesinato en un pequeño pueblo');

-- Insertar más autores de ejemplo
INSERT INTO authors (name, nationality, birth_date, biography) VALUES
('Julio Cortázar', 'Argentino', '1914-08-26', 'Escritor, traductor e intelectual argentino. Es uno de los autores más innovadores y originales de su tiempo, maestro del relato corto, la prosa poética y la narración breve.'),
('Jorge Luis Borges', 'Argentino', '1899-08-24', 'Escritor de cuentos, ensayos, poemas y guiones de cine argentino. Una de las figuras clave tanto de la literatura en español como universal.'),
('Pablo Neruda', 'Chileno', '1904-07-12', 'Poeta y político chileno, considerado entre los más destacados e influyentes artistas de su siglo.'),
('Octavio Paz', 'Mexicano', '1914-03-31', 'Poeta, ensayista y diplomático mexicano. Obtuvo el premio Nobel de literatura en 1990.');

-- Insertar más libros de ejemplo
INSERT INTO books (title, isbn, author_id, publication_year, genre, publisher, total_copies, available_copies, description) VALUES
('Rayuela', '978-8437604923', 4, 1963, 'Novela', 'Editorial Sudamericana', 3, 3, 'Una obra que revolucionó la narrativa en español.'),
('Ficciones', '978-8420674612', 5, 1944, 'Cuentos', 'Editorial Sur', 4, 4, 'Colección de cuentos que explora temas como el tiempo, la realidad y la identidad.'),
('Veinte poemas de amor y una canción desesperada', '978-9561120980', 6, 1924, 'Poesía', 'Editorial Nascimento', 5, 5, 'Uno de los libros de poesía más leídos en español.'),
('El laberinto de la soledad', '978-9681608111', 7, 1950, 'Ensayo', 'Fondo de Cultura Económica', 3, 3, 'Análisis profundo de la identidad mexicana.');