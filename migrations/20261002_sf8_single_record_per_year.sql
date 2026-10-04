-- Apply after resolving existing duplicate (lrn, school_year) groups.
-- ARH, tobacco, and deworming/WIFA forms hold one row per learner and school year.
ALTER TABLE arh_records DROP INDEX idx_unique_arh,
    ADD UNIQUE KEY idx_unique_arh (lrn, school_year);
ALTER TABLE tobacco_control_records DROP INDEX idx_unique_tobacco,
    ADD UNIQUE KEY idx_unique_tobacco (lrn, school_year);
ALTER TABLE deworming_wifa_records
    ADD UNIQUE KEY idx_unique_deworming_wifa (lrn, school_year);
