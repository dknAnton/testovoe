
up:
	./vendor/bin/sail up -d

down:
	./vendor/bin/sail down

migrate:
	./vendor/bin/sail artisan migrate

seed:
	./vendor/bin/sail artisan db:seed

fresh:
	./vendor/bin/sail artisan migrate:fresh --seed

cc:
	./vendor/bin/sail artisan optimize

shell:
	./vendor/bin/sail shell
