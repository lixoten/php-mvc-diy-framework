---
applyTo: '**'
---

When you see me Sanitize, Validate make sure i am not mixing concerns.
if so please warn me if you see this.

This is a new framework being built from scratch.
I want things done correctly that follow best modern SOLID framework standards.
No hacks,
SOLID Principles, reuse code.
if a Service should be used, please suggest an idea.
if u know of a good pattern to use, please suggest it.

(SOLID Services) please append "Service" as in XxxxxService.

### The `ListView` has one clear responsibility: to act as a data container that represents a fully configured list, ready for rendering. It holds the data, columns, actions, and options, but it contains no logic on how that data was created or how it should be displayed as HTML.
- `ListInterface` defines a clear and focused contract for what a "List" object is. Any part of your application that needs to interact with a list (like a renderer) can depend on this interface without needing to know about the concrete ListView class.
This is a very clean architecture.
- `ListFactory` Its sole responsibility is to construct and configure a ListView object. It orchestrates the process by using a ListType to define the list structure and PaginationService to process pagination data.
- `ListRenderer` is responsible for drawing it. AbstractListRenderer and BootstrapListRenderer: These classes are exclusively focused on rendering the ListView into HTML. AbstractListRenderer provides the common rendering logic and structure, while BootstrapListRenderer implements the specific HTML output using Bootstrap components. They consume the ListView data but do not modify it or participate in its creation.
