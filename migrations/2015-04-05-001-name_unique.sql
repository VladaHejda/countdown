ALTER TABLE countdown
	DROP INDEX name,
	ADD UNIQUE (name);
