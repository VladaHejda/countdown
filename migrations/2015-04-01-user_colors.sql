ALTER TABLE countdown
		ADD background CHAR(6) NOT NULL DEFAULT '000000' AFTER expiration,
		ADD color CHAR(6) NOT NULL DEFAULT 'ffffff' AFTER background;
