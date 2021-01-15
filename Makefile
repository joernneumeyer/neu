

dev-live:
	php -S [::]:8000 dev-router.php -t public

coverage:
	php -S [::]:8001 -t coverage
