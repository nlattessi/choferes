SELECT
chofer.id as choferId, chofer.nombre, chofer.apellido, chofer.dni,
chofer.tiene_curso_basico as tieneCursoBasico,
chofer_curso.id as choferCursoId, chofer_curso.is_aprobado as aprobado, chofer_curso.pagado,
chofer_curso.documentacion, curso.id as cursoId, curso.fecha_fin as fechaFin
FROM chofer
LEFT JOIN chofer_curso ON chofer_curso.chofer_id = chofer.id
LEFT JOIN curso ON chofer_curso.curso_id = curso.id
WHERE curso.tipoCurso_id = 1 AND chofer.tiene_curso_basico = 0 AND chofer_curso.is_aprobado = 1 AND chofer_curso.pagado = 1
ORDER BY curso.fecha_fin DESC;
