# AlMadar Bank E-Banking API - UML Use Case Diagram

```mermaid
```

## Diagram Explanation

### **Actors Identified:**

**Primary Actors:**
- **Visitor**: Unauthenticated user who can register
- **Client**: Authenticated bank customer with full access to their accounts
- **Guardian**: Adult user responsible for minor account operations (specialized Client)
- **Co-Owner**: User sharing joint account access (specialized Client)
- **Administrator**: Bank staff with administrative privileges

**Secondary Actors:**
- **Frontend Web/Mobile Applications**: Consumer applications using the API
- **JWT Authentication Service**: External service for token management
- **External Banking System**: For inter-banking operations

### **Use Cases Categorized:**

**Authentication & User Management:**
- User registration, authentication, token management
- Profile viewing and updates

**Account Management:**
- Account creation, viewing, co-owner management
- Guardian assignment and minor account conversion
- Account closure requests

**Transfer Operations:**
- Transfer initiation and viewing
- Transaction history and details

**Administrative Functions:**
- Account blocking, unblocking, and closure
- System-wide account listing

**Automated Processes:**
- Monthly fee processing
- Interest calculation
- Transfer rule validation

### **Key Relationships:**

**<<include>> relationships** show mandatory dependencies like authentication requirements
**<<extend>> relationships** show optional behaviors like guardian assignment for minor accounts
**Generalization** shows that Guardian and Co-Owner are specialized types of Client
**Time-based triggers** handle automated monthly processes

The diagram strictly follows UML 2.x standards with proper actor notation, use case naming conventions (verb-noun phrases), and relationship indicators.
