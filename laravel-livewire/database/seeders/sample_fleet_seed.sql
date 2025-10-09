-- Sample data for drivers, trucks, and clients (PostgreSQL)
BEGIN;

-- Insert drivers
INSERT INTO drivers (
    name,
    last_name,
    document_number,
    license_number,
    license_expiration,
    phone,
    email,
    address,
    status,
    work_schedule,
    notes,
    created_at,
    updated_at
) VALUES
    ('Carlos', 'Ramírez', 'DNI900001', 'LIC-PER-001', '2027-08-15', '+51 900 111 111', 'carlos.ramirez@example.com', 'Av. Javier Prado 123, Lima', 'available', NULL, 'Especialista en rutas largas.', NOW(), NOW()),
    ('María', 'Quispe', 'DNI900002', 'LIC-PER-002', '2026-05-30', '+51 900 222 222', 'maria.quispe@example.com', 'Jr. Arequipa 456, Cusco', 'assigned', NULL, 'Con experiencia en transporte refrigerado.', NOW(), NOW()),
    ('José', 'Huamán', 'DNI900003', 'LIC-PER-003', '2028-11-20', '+51 900 333 333', 'jose.huaman@example.com', 'Calle Lima 789, Arequipa', 'standby', NULL, 'Disponible para turnos nocturnos.', NOW(), NOW()),
    ('Lucía', 'Paredes', 'DNI900004', 'LIC-PER-004', '2025-12-10', '+51 900 444 444', 'lucia.paredes@example.com', 'Av. La Marina 321, Callao', 'available', NULL, 'Experta en documentación SUNAT.', NOW(), NOW()),
    ('Renzo', 'Salazar', 'DNI900005', 'LIC-PER-005', '2029-03-05', '+51 900 555 555', 'renzo.salazar@example.com', 'Mz. B Lote 9, Trujillo', 'on_route', NULL, 'Encargado de rutas norte.', NOW(), NOW())
ON CONFLICT (document_number) DO NOTHING;

-- Insert trucks
INSERT INTO trucks (
    plate_number,
    brand,
    model,
    year,
    type,
    capacity,
    status,
    last_maintenance,
    next_maintenance,
    technical_details,
    mileage,
    maintenance_interval_days,
    maintenance_mileage_threshold,
    last_maintenance_mileage,
    created_at,
    updated_at
) VALUES
    ('ABC-123', 'Volvo', 'FH16', 2022, 'Tracto', 32.50, 'available', '2024-03-01', '2024-06-01', 'Motor D16K Euro 5, GPS integrado.', 125000, 90, 10000, 120000, NOW(), NOW()),
    ('XYZ-987', 'Scania', 'R500', 2021, 'Tracto', 30.00, 'assigned', '2024-02-10', '2024-05-10', 'Sistema de frenos ABS, telemetría satelital.', 142500, 120, 12000, 135000, NOW(), NOW())
ON CONFLICT (plate_number) DO NOTHING;

-- Insert clients
INSERT INTO clients (
    business_name,
    tax_id,
    contact_name,
    email,
    phone,
    billing_address,
    payment_terms,
    notes,
    created_at,
    updated_at
) VALUES
    ('Logística Andina SAC', '20512345001', 'Rocío Chávez', 'contacto@logisticaandina.pe', '+51 1 500 0001', 'Av. República de Panamá 3500, Lima', '30 días', 'Cliente premium, requiere reportes semanales.', NOW(), NOW()),
    ('Distribuciones Pacífico SRL', '20665432012', 'Alfredo Milla', 'ventas@distribucionespacifico.pe', '+51 1 500 0002', 'Jr. Piura 145, Chiclayo', '15 días', NULL, NOW(), NOW()),
    ('Agroexportadora El Valle', '20456789013', 'Cynthia Torres', 'cthorres@agroelvalle.pe', '+51 74 600 0003', 'Car. Panamericana Norte Km 9, Piura', '45 días', 'Solicitan transporte refrigerado.', NOW(), NOW()),
    ('Consorcio Minero Sur', '20598765014', 'Diego Alarcón', 'dalarcon@cmsur.com', '+51 54 700 0004', 'Av. Bolognesi 123, Arequipa', '60 días', 'Necesita unidades con permisos especiales.', NOW(), NOW()),
    ('Textiles Andinos SAC', '20123456015', 'Gabriela Cuellar', 'gcuellar@textilesandinos.pe', '+51 1 500 0005', 'Av. La Molina 2400, Lima', '30 días', NULL, NOW(), NOW()),
    ('Constructora Norte SAC', '20567891016', 'Eduardo Reyes', 'ereyes@constructnorte.pe', '+51 44 600 0006', 'Av. Independencia 500, Trujillo', '45 días', 'Proyectos en la sierra norte.', NOW(), NOW()),
    ('Pesquera Pacífico Azul', '20654321017', 'María Rodríguez', 'mrodriguez@pesqpacifico.pe', '+51 73 600 0007', 'Av. Grau 801, Chimbote', '30 días', 'Transporte de productos congelados.', NOW(), NOW()),
    ('Retail Express EIRL', '20432109818', 'Ricardo Campos', 'rcampos@retailexpress.pe', '+51 1 500 0008', 'Av. Angamos Este 1555, Lima', '15 días', NULL, NOW(), NOW()),
    ('Farmacéutica Salud Total', '20543210919', 'Lorena Vega', 'lvega@saludtotal.pe', '+51 1 500 0009', 'Av. Brasil 999, Lima', '30 días', 'Exigen monitoreo de temperatura.', NOW(), NOW()),
    ('Corporación EnerSur', '20678901220', 'Fernando Paredes', 'fparedes@enersur.pe', '+51 56 600 0010', 'Av. Tacna 450, Tacna', '60 días', NULL, NOW(), NOW()),
    ('Exportaciones Amazónicas SAC', '20521098721', 'Julio Pacheco', 'jpacheco@expamazonicas.pe', '+51 65 600 0011', 'Av. Abelardo Quiñones 250, Iquitos', '45 días', 'Carga mixta, requiere coordinación fluvial.', NOW(), NOW()),
    ('Servicios Industriales del Sur', '20530987622', 'Vanessa Herrera', 'vherrera@sisur.pe', '+51 51 600 0012', 'Av. La Cultura 600, Puno', '30 días', NULL, NOW(), NOW()),
    ('Horeca Supply Perú', '20590817223', 'Esteban Rojas', 'er@horecasupply.pe', '+51 1 500 0013', 'Av. Caminos del Inca 821, Lima', '15 días', 'Entrega nocturna preferida.', NOW(), NOW()),
    ('TecnoImport SAC', '20600123424', 'Natalia Salas', 'nsalas@tecnoimport.pe', '+51 1 500 0014', 'Av. Canadá 1350, Lima', '30 días', NULL, NOW(), NOW()),
    ('Centro Logístico Integrado', '20611223325', 'Pablo Guzmán', 'pguzman@centrolog.pe', '+51 1 500 0015', 'Av. Néstor Gambetta Km 15, Callao', '60 días', 'Contrato marco hasta 2025.', NOW(), NOW())
ON CONFLICT (tax_id) DO NOTHING;

COMMIT;
