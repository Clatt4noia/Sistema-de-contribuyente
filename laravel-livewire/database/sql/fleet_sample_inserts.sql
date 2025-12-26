-- Sample seed data for fleet entities: drivers, trucks, and clients
-- Safe to run multiple times thanks to ON CONFLICT clauses.

BEGIN;

-- Insert drivers
INSERT INTO drivers (name, last_name, document_number, license_number, license_expiration, phone, email, address, status, notes)
VALUES
    ('Juan', 'Perez', 'DNI00112233', 'LIC-PER-2025', '2026-04-01', '+51 900 111 111', 'juan.perez@example.com', 'Av. Arequipa 123, Lima', 'available', 'Chofer con experiencia en rutas de la sierra.'),
    ('Maria', 'Lopez', 'DNI00445566', 'LIC-LOP-2030', '2027-08-15', '+51 900 222 222', 'maria.lopez@example.com', 'Jr. Trujillo 456, Trujillo', 'available', 'Especialista en transporte refrigerado.'),
    ('Carlos', 'Ramos', 'DNI00778899', 'LIC-RAM-2028', '2025-12-31', '+51 900 333 333', 'carlos.ramos@example.com', 'Calle Cusco 789, Arequipa', 'on_trip', 'Actualmente asignado a ruta Arequipa - Cusco.'),
    ('Ana', 'Gutierrez', 'DNI00990011', 'LIC-GUT-2027', '2028-06-20', '+51 900 444 444', 'ana.gutierrez@example.com', 'Av. Brasil 321, Lima', 'available', 'Certificada en manejo defensivo.'),
    ('Luis', 'Salazar', 'DNI00113344', 'LIC-SAL-2026', '2027-11-10', '+51 900 555 555', 'luis.salazar@example.com', 'Av. Grau 654, Chiclayo', 'inactive', 'En proceso de evaluación médica.')
ON CONFLICT (document_number) DO UPDATE
SET
    name = EXCLUDED.name,
    last_name = EXCLUDED.last_name,
    license_number = EXCLUDED.license_number,
    license_expiration = EXCLUDED.license_expiration,
    phone = EXCLUDED.phone,
    email = EXCLUDED.email,
    address = EXCLUDED.address,
    status = EXCLUDED.status,
    notes = EXCLUDED.notes,
    updated_at = NOW();

-- Insert trucks
INSERT INTO trucks (plate_number, brand, model, year, type, capacity, mileage, status, last_maintenance, next_maintenance, technical_details)
VALUES
    ('ABC-123', 'Volvo', 'FH16', 2022, 'Tracto', 30.50, 45000, 'available', '2024-01-10', '2024-07-10', 'Configuración 6x4, motor D16K de 550 HP.'),
    ('XYZ-987', 'Scania', 'R500', 2021, 'Tracto', 28.00, 52000, 'maintenance', '2023-12-20', '2024-06-20', 'Incluye telemetría conectada y paquete de seguridad premium.')
ON CONFLICT (plate_number) DO UPDATE
SET
    brand = EXCLUDED.brand,
    model = EXCLUDED.model,
    year = EXCLUDED.year,
    type = EXCLUDED.type,
    capacity = EXCLUDED.capacity,
    mileage = EXCLUDED.mileage,
    status = EXCLUDED.status,
    last_maintenance = EXCLUDED.last_maintenance,
    next_maintenance = EXCLUDED.next_maintenance,
    technical_details = EXCLUDED.technical_details,
    updated_at = NOW();

-- Insert clients
INSERT INTO clients (business_name, tax_id, contact_name, email, phone, billing_address, payment_terms, notes)
VALUES
    ('Logistica Andina SAC', '20512345671', 'Rosa Valdez', 'rvaldez@logisticaandina.pe', '+51 1 555 0101', 'Av. Javier Prado Este 1234, San Isidro', '30 días', 'Cliente principal para rutas Lima - Cusco.'),
    ('Transportes del Norte SRL', '20456789120', 'Hector Ruiz', 'hruiz@transnorte.com', '+51 74 456 789', 'Av. Independencia 456, Chiclayo', '45 días', 'Solicita unidades con GPS en tiempo real.'),
    ('Comercial Pacífico EIRL', '20678912345', 'Laura Medina', 'lmedina@compacifico.pe', '+51 56 345 678', 'Calle Lima 567, Piura', 'Contado', 'Requiere entregas nocturnas.'),
    ('AgroExport Peru SAC', '20567891234', 'Miguel Herrera', 'mherrera@agroexport.pe', '+51 44 765 432', 'Carretera Panamericana Km 12, Trujillo', '60 días', 'Productos perecibles, necesita cadena de frío.'),
    ('Minerales del Sur SA', '20456712389', 'Patricia Flores', 'pflores@minsursur.com', '+51 84 234 567', 'Av. La Cultura 890, Cusco', '30 días', 'Rutas de alta montaña.'),
    ('Retail Express SAC', '20671234589', 'Diego Soto', 'dsoto@retailexpress.pe', '+51 1 777 1212', 'Av. La Marina 222, Callao', '15 días', 'Entregas urbanas con ventanas estrechas.'),
    ('Construcciones Andinas SA', '20589123456', 'Sandra Rojas', 'srojas@conandinas.com', '+51 1 765 4321', 'Av. Los Ingenieros 890, Surco', '45 días', 'Carga pesada con escolta.'),
    ('Pesquera del Pacífico SAC', '20523456789', 'Luis Cabrera', 'lcabrera@pespacifico.pe', '+51 1 234 5678', 'Muelle Industrial 456, Chimbote', '30 días', 'Transporte refrigerado urgente.'),
    ('Energia Limpia Peru SAC', '20634567890', 'Carmen Zapata', 'czapata@energialimpia.pe', '+51 1 876 5432', 'Av. Primavera 123, San Borja', '30 días', 'Equipos sensibles al polvo.'),
    ('Quimicos del Centro SRL', '20467891235', 'Oscar Villanueva', 'ovillanueva@quimcentro.pe', '+51 64 234 890', 'Av. Ferrocarril 345, Huancayo', '60 días', 'Materiales peligrosos clase 3.'),
    ('Exportadora Selva Verde SAC', '20591234567', 'Adriana Flores', 'aflores@selvaverde.pe', '+51 65 456 789', 'Jr. Amazonas 123, Iquitos', '90 días', 'Requiere transporte multimodal.'),
    ('Industrias Metalicas SRL', '20478912356', 'Ricardo Paredes', 'rparedes@indmet.pe', '+51 1 345 9876', 'Av. Colonial 678, Lima', '30 días', 'Carga sobredimensionada.'),
    ('Distribuciones Centro Sur SAC', '20612345678', 'Veronica Nieto', 'vnieto@dcsur.pe', '+51 66 567 890', 'Av. Los Héroes 890, Ayacucho', '45 días', 'Ventanas de entrega flexibles.'),
    ('Café Andino Export SRL', '20534567891', 'Jorge Castañeda', 'jcastaneda@cafeandino.pe', '+51 1 908 0707', 'Jr. Huánuco 234, Huánuco', '30 días', 'Carga frágil, requiere inspección.'),
    ('Textiles del Sol SAC', '20567813459', 'Elena Navarro', 'enavarro@textilsol.pe', '+51 1 606 0606', 'Av. Nicolás Ayllón 765, Ate', '30 días', 'Solicita seguimiento diario por correo.')
ON CONFLICT (tax_id) DO UPDATE
SET
    business_name = EXCLUDED.business_name,
    contact_name = EXCLUDED.contact_name,
    email = EXCLUDED.email,
    phone = EXCLUDED.phone,
    billing_address = EXCLUDED.billing_address,
    payment_terms = EXCLUDED.payment_terms,
    notes = EXCLUDED.notes,
    updated_at = NOW();

COMMIT;
