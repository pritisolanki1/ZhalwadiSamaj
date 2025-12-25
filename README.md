https://www.getpostman.com/collections/82697de36b80484b07c0

Make Request=php artisan make:request NAMEOFFILE

php artisan make:migration create_user_table --create=result

php artisan make:migration add_type_medium_to_result_table --table=result

Make model,controller,datatable,factory,seed=php artisan make:model NAMEOFFILE & firstlettercapital -a

delete the column=php artisan make:migration droup_grade_to_results --table=results

php artisan migrate

php artisan key:generate

php artisan passport:install --force

php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

php artisan l5-swagger:generate

php artisan db:seed --class=RoleSeeder

=================================================================================================
php artisan make:model Name -a , by using this we can make controller,model,factory,migration,seed file then php artisan
make:request Name ,by this you can make request.after this we have to shift the controller to our folder after shifting
we have to change the path of namespace, controller extends from ,and models path and after shifting the model in our
folder we have to change the path of the namespace and have to change USE path .
==================================================================================================
php artisan laravel-swagger:generate > public/docs/api-docs.json
==================================================================================================
{BASE_URL}/api/docs
==================================================================================================

