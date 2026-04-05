# Output formats: readable vs normalized

XmlExtractKit exposes two main output styles.

## `prettyPrint()`: readable output

Best when you want to move XML data into ordinary application code
quickly.

### Good for

- JSON serialization;
- logs and debug dumps;
- queue payloads;
- controllers, services, and DTO mappers that already expect 
  associative arrays.

### Shape

```php
[
    'offer' => [
        '@attributes' => [
            'id' => '206111',
            'available' => 'true',
        ],
        'name' => 'USB-C Dock',
        'price' => [
            '@value' => '129.90',
            '@attributes' => [
                'currency' => 'USD',
            ],
        ],
        'picture' => [
            'https://cdn.example.test/1.jpg',
            'https://cdn.example.test/2.jpg',
        ],
    ],
]
```

### Rules of thumb

- repeated child tags become indexed arrays;
- element text goes into `@value` when attributes are present;
- attributes stay grouped under `@attributes`.

## `convert()`: normalized hierarchy

Best when you want a predictable structure for traversal, wrappers, 
or internal adapters.

### Good for

- `XmlElement`;
- reusable traversal logic;
- pipelines that need explicit names, values, and child sequences;
- custom notations via `XmlConverter` or `XmlParser`.

### Shape

```php
[
    'n' => 'feed',
    'a' => [
        'generated_at' => '2026-03-28T09:00:00Z',
    ],
    's' => [
        [
            'n' => 'offer',
            'a' => [
                'id' => '206111',
                'available' => 'true',
            ],
            's' => [
                [
                    'n' => 'name',
                    'v' => 'USB-C Dock',
                ],
                [
                    'n' => 'price',
                    'v' => '129.90',
                    'a' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
        ],
    ],
]
```

### Rules of thumb

- `n` = element name;
- `v` = direct text value;
- `a` = attributes;
- `s` = child sequence;
- every node keeps the same overall contract.
