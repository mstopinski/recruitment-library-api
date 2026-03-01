# Library API

REST API do zarządzania biblioteką – książki i autorzy. Zbudowane w Laravel 12.

## Instalacja i uruchomienie

```bash
docker-compose up -d --build
docker exec -it laravel_app php artisan db:seed  # Wyświetli token API potrzebny do autoryzacji
```

Entrypoint automatycznie wykonuje: `composer install`, kopiowanie `.env`, `key:generate` i `migrate`.
Queue worker startuje automatycznie jako osobny kontener.

API dostępne pod `http://localhost:8000/api`

Po seedowaniu w konsoli pojawi się token API potrzebny do autoryzacji (`Authorization: Bearer {token}`).

## Postman

Kolekcja do importu znajduje się w pliku `Library_API.postman_collection.json`. Po zaimportowaniu ustaw zmienną `token` na wartość otrzymaną z seedera.

## Testy

```bash
docker exec -it laravel_app php artisan test
```


## Decyzje architektoniczne

### Service Layer

Logika biznesowa została wydzielona do klas `BookService` i `AuthorService`. Kontrolery odpowiadają wyłącznie za obsługę HTTP (walidacja, odpowiedzi), a serwisy za operacje na danych. Dzięki temu kod jest łatwiejszy do testowania.

### Dispatch UpdateAuthorLastBook przy tworzeniu, edycji i usuwaniu

Job `UpdateAuthorLastBook` jest dispatchowany nie tylko przy dodawaniu książki, ale też przy edycji i usuwaniu. Kolumna `last_book_title` na modelu `Author` powinna zawsze odzwierciedlać aktualny stan. Jeśli książka zostanie usunięta lub autor zostanie odłączony, tytuł ostatniej książki musi zostać zaktualizowany aby dane były spójne.