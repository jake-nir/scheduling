---
name: database-designer
description: Design, review, and improve MySQL or MariaDB databases including tables, relationships, foreign keys, indexes, constraints, migrations, and queries. Use when creating or modifying a system database.
---

# Database Designer

## Goals

Create reliable, normalized and maintainable relational databases.

## Process

1. Identify system entities.
2. Identify relationships.
3. Define tables.
4. Define primary keys.
5. Define foreign keys.
6. Define required fields.
7. Define appropriate data types.
8. Add unique constraints where necessary.
9. Add indexes for commonly searched fields.
10. Review normalization.
11. Review deletion/update behavior.

## Rules

Prefer:

- INT/BIGINT for identifiers where appropriate
- VARCHAR for bounded text
- TEXT for long text
- DATETIME/TIMESTAMP for timestamps
- DECIMAL for financial values
- BOOLEAN/TINYINT for true/false values

Do not store multiple unrelated values in one database field.

Avoid duplicated data where a relationship can represent it properly.

## Audit Fields

Where appropriate, include:

- created_at
- updated_at
- created_by
- updated_by

For important transactions, maintain history/audit tables.

## Security

Never store plaintext passwords.

Use appropriate constraints and server-side validation.

## Output

When designing a database provide:

- ER-style relationship explanation
- Table list
- Field list
- Primary keys
- Foreign keys
- Indexes
- SQL creation scripts when requested