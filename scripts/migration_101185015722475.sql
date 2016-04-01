UPDATE chofer_curso
SET is_aprobado = true
WHERE aprobado > 3;

UPDATE chofer
SET nombre = 'JOSE RENZO', apellido = 'GONZALEZ OJEDA'
WHERE dni = 36796263;

UPDATE chofer
SET apellido = CONCAT(LEFT(apellido, CHAR_LENGTH(apellido) -1), '')
WHERE apellido LIKE '%,%';
