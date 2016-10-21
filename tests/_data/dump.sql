DROP TABLE IF EXISTS sample_tags;
DROP TABLE IF EXISTS sample_property;
DROP TABLE IF EXISTS sample_categories;
DROP TABLE IF EXISTS sample_table;
-- DROP TABLE IF EXISTS taxonomy_def;
-- DROP TABLE IF EXISTS taxonomy_terms;

DELETE FROM taxonomy_test.sample_tags;
DELETE FROM taxonomy_test.sample_property;
DELETE FROM taxonomy_test.sample_categories;
DELETE FROM taxonomy_test.taxonomy_def;

CREATE TABLE sample_table
(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  name TEXT NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO sample_table (id, name) VALUES (1, 'record1');
INSERT INTO sample_table (id, name) VALUES (2, 'record2');
INSERT INTO sample_table (id, name) VALUES (3, 'record3');