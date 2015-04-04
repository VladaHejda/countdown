ALTER TABLE countdown
	CHANGE story epilogue VARCHAR(300) COLLATE utf8_unicode_ci NOT NULL,
	ADD prologue VARCHAR(300) COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER name;
