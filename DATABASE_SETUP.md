# Database Setup & Seed Data

This document provides seed data and initialization instructions for lookup tables in the AMS database.

## Seed Data SQL

Execute the following SQL statements to populate empty lookup tables:

### Distance Learning Modalities

The `distance_learning_modalities` table is required for enrollment form functionality. Execute these INSERT statements:

```sql
INSERT INTO distance_learning_modalities (name, description, is_active) VALUES
('In-Person Learning', 'Traditional face-to-face classroom instruction', 1),
('Blended Learning', 'Combination of in-person and online instruction', 1),
('Synchronous Online Learning', 'Real-time online instruction (live classes)', 1),
('Asynchronous Online Learning', 'Self-paced online instruction (pre-recorded, materials)', 1),
('Hybrid Learning', 'Mix of synchronous and asynchronous online components', 1),
('Distance Learning', 'Fully remote instruction without in-person meetings', 1),
('Remote Learning', 'Temporary remote learning (emergency protocols)', 1),
('Modular Learning', 'Module-based self-paced learning materials', 1);
```

### Other Lookup Tables (Reference)

These tables should already have seed data, but are listed for reference:

**Religions** (if empty):
```sql
INSERT INTO religions (name, is_active) VALUES
('Roman Catholic', 1),
('Protestant', 1),
('Islam', 1),
('Buddhism', 1),
('Hindu', 1),
('Indigenous Beliefs', 1),
('Others', 1);
```

**Special Needs Types** (if empty):
```sql
-- Diagnosis category
INSERT INTO special_needs_types (name, category, is_active) VALUES
('Autism Spectrum Disorder', 'Diagnosis', 1),
('Attention Deficit/Hyperactivity Disorder (ADHD)', 'Diagnosis', 1),
('Learning Disability', 'Diagnosis', 1),
('Intellectual Disability', 'Diagnosis', 1),
('Cerebral Palsy', 'Diagnosis', 1),
('Hearing Impairment', 'Diagnosis', 1),
('Visual Impairment/Blind', 'Diagnosis', 1),
('Speech/Language Disorder', 'Diagnosis', 1),
('Emotional/Behavioral Disorder', 'Diagnosis', 1),
('Multiple Disabilities', 'Diagnosis', 1),
('Orthopedic/Physical Handicap', 'Diagnosis', 1),
('Special Health Problem/Chronic Disease', 'Diagnosis', 1);

-- Manifestation category
INSERT INTO special_needs_types (name, category, is_active) VALUES
('Difficulty in Communicating', 'Manifestation', 1),
('Difficulty in Seeing', 'Manifestation', 1),
('Difficulty in Hearing', 'Manifestation', 1),
('Difficulty in Mobility', 'Manifestation', 1),
('Difficulty in Remembering', 'Manifestation', 1),
('Difficulty in Performing Adaptive Skills', 'Manifestation', 1),
('Difficulty in Applying Knowledge', 'Manifestation', 1),
('Difficulty in Displaying Interpersonal Behavior', 'Manifestation', 1);
```

## Verification Steps

After executing seed data, verify that records were inserted:

```sql
-- Check distance learning modalities
SELECT * FROM distance_learning_modalities WHERE is_active = 1;

-- Check religions
SELECT * FROM religions WHERE is_active = 1;

-- Check special needs types
SELECT * FROM special_needs_types WHERE is_active = 1;
```

## Adding More Lookup Values

To add new lookup values through the admin interface:

1. **Via API Endpoints** (CRUD operations):
   - POST to `/api/crud/distance_learning_modalities/c_distance_learning_modalities.php`
   - POST to `/api/crud/religions/c_religions.php`
   - POST to `/api/crud/special_needs_types/c_special_needs_types.php`

2. **Via Direct SQL**:
   ```sql
   INSERT INTO distance_learning_modalities (name, description, is_active) 
   VALUES ('New Modality', 'Description here', 1);
   ```

## Fresh Install Checklist

When setting up AMS on a fresh database:

- [ ] Database and all tables created (via schema files)
- [ ] Distance Learning Modalities populated
- [ ] Religions populated (if needed)
- [ ] Special Needs Types populated (both diagnosis and manifestation)
- [ ] Other lookup tables verified to have data
- [ ] Admin user created for initial access
- [ ] config.php database credentials updated (if not local dev)
- [ ] Server environment permissions set correctly

## Important Notes

- All lookup tables use `is_active` flag for soft deletes (don't actually delete records)
- Lookup values are referenced via foreign keys in enrollment and medical records
- Enrollment form will show empty dropdowns if seed data is not populated
- API validation will reject invalid lookup IDs before insertion (prevents FK errors)
