# Library API

REST API do zarządzania biblioteką – książki i autorzy. Zbudowane w Laravel 12.

## Wymagania

Docker

## Instalacja i uruchomienie

```bash
docker-compose up -d --build
```

Entrypoint automatycznie wykonuje: `composer install`, kopiowanie `.env`, `key:generate` i `migrate`.

**Uwaga:** Przy pierwszym uruchomieniu kontener potrzebuje chwili na instalację zależności i migrację bazy danych. Przed wykonaniem kolejnych komend upewnij się, że proces się zakończył:

```bash
docker logs -f laravel_app
```

Gdy zobaczysz `INFO  Server running on `, kontener jest gotowy. Wtedy możesz uruchomić seeder:

```bash
docker exec -it laravel_app php artisan db:seed  # Wyświetli token API potrzebny do autoryzacji
```
Queue worker startuje automatycznie jako osobny kontener.

API dostępne pod `http://localhost:8000/api`

Po seedowaniu w konsoli pojawi się token API potrzebny do autoryzacji (`Authorization: Bearer {token}`).

## Endpointy

### Publiczne

| Metoda | Endpoint | Opis |
|--------|----------|------|
| GET | `/api/books` | Lista książek |
| GET | `/api/books/{id}` | Szczegóły książki |
| GET | `/api/authors` | Lista autorów |
| GET | `/api/authors/{id}` | Szczegóły autora |

### Zabezpieczone (wymagają `Authorization: Bearer {token}`)

| Metoda | Endpoint | Opis |
|--------|----------|------|
| POST | `/api/books` | Dodanie książki |
| PUT | `/api/books/{id}` | Edycja książki |
| DELETE | `/api/books/{id}` | Usunięcie książki |

## Postman

Kolekcja do importu znajduje się w pliku `Library_API.postman_collection.json`. Po zaimportowaniu ustaw zmienną `token` na wartość otrzymaną z seedera.

## Komenda Artisan

Tworzenie autora z poziomu CLI:

```bash
# Tryb interaktywny – komenda zapyta o imię i nazwisko
docker exec -it laravel_app php artisan author:create

# Podanie argumentów bezpośrednio
docker exec -it laravel_app php artisan author:create "Adam" "Mickiewicz"

# Pominięcie sprawdzania duplikatów
docker exec -it laravel_app php artisan author:create "Adam" "Mickiewicz" --force
```

Komenda automatycznie sprawdza czy autor o podanym imieniu i nazwisku już istnieje. Flagą `--force` można pominąć to sprawdzenie.

## Testy

```bash
docker exec -it laravel_app php artisan test
```


## Decyzje architektoniczne

### Service Layer

Logika biznesowa została wydzielona do klas `BookService` i `AuthorService`. Kontrolery odpowiadają wyłącznie za obsługę HTTP (walidacja, odpowiedzi), a serwisy za operacje na danych. Dzięki temu kod jest łatwiejszy do testowania.

### Dispatch UpdateAuthorLastBook przy tworzeniu, edycji i usuwaniu

Job `UpdateAuthorLastBook` jest dispatchowany nie tylko przy dodawaniu książki, ale też przy edycji i usuwaniu. Kolumna `last_book_title` na modelu `Author` powinna zawsze odzwierciedlać aktualny stan. Jeśli książka zostanie usunięta lub autor zostanie odłączony, tytuł ostatniej książki musi zostać zaktualizowany aby dane były spójne.
