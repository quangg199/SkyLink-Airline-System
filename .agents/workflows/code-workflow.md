---
description:
---

# AGENT WORKFLOW: THE TWO-PHASE VERIFICATION PROCESS

You must execute every feature request from the user through the following strict 4-step workflow. You are NOT allowed to bypass any step.

---

### PHASE 1: ARCHITECTURAL DESIGN & APPROVAL

#### STEP 1: PROBLEM-FIRST DIAGNOSIS

- Whenever the user requests a new feature or refactoring, STOP immediately. DO NOT write code.
- Analyze the request and output a brief analysis containing:
  1. **Core Problem/Complexity:** What is the technical pain point of this feature? (e.g., tight coupling, risky state transition, complex conditional branch).
  2. **YAGNI & Anti-Over-Engineering Check:** Is this feature genuinely complex enough to require an abstraction/pattern? Or can it be solved with a simple, clean, direct function?
- **Action:** Wait for user confirmation or proceed to Step 2 if requested.

#### STEP 2: DESIGN PATTERN MAPPING & PROPOSAL

- If a pattern is necessary, explicitly declare which pattern(s) from the allowed list will be used.
- Provide a brief structural layout of the new files/classes to be created (e.g., Target Interface, Concrete Implementations, Service Context).
- Explain **WHY** this specific pattern solves the problem defined in Step 1.
- **CRITICAL COMPLIANCE HALT:** You must print the text: `[WAITING FOR ARCHITECT'S APPROVAL]` at the end of this step and STOP generating. You must wait for the user's explicit signal (e.g., "Approved", "Go ahead", "Proceed") before moving to Phase 2.

---

### PHASE 2: IMPLEMENTATION & QUALITY ASSURANCE

#### STEP 3: PRODUCTION-READY CODING

- Once approved by the user, generate the complete, production-ready code.
- Ensure all constraints from the System Rules are met:
  1. Strict type-hinting, explicit return types, and self-documenting naming conventions.
  2. Database mutations wrapped cleanly inside `DB::beginTransaction()`, `commit()`, and `rollBack()`.
  3. High-latency operations (Email, external API syncs) delegated to background queues via `ShouldQueue`.

#### STEP 4: CODE EXPLANATION & ARCHITECTURAL REVIEW

- After outputting the code, explain the execution flow line-by-line or component-by-component.
- Explicitly state how the chosen pattern operates at runtime within the system boundary.
- Point out any potential performance or concurrency considerations (e.g., race conditions on seat bookings, database index requirements).
