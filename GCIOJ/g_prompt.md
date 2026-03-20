# Antigravity Problem Generation Task

**Read the information below and generate a new programming problem in the workspace.**

## 1. File & Folder Structure
- Target Directory: Create a new folder at `problemset/[ID]/`.
- Required Files: Create exactly 3 files inside that folder:
  1. `[ID].html`
  2. `[ID].py`
  3. `[ID]_Template.py`

## 2. Problem Information
- **ID:** [e.g., 805]
- **Description:** [Brief description of the problem. e.g., Check if an array is strictly increasing.]
- **Function Name:** `[e.g., solve]`
- **Input Parameters:** `[e.g., nums for array, number for number, matrix for matrix]`
- **Return Type:** `[e.g., boolean]`
- **Match the input parameter into the solve function**

## 3. Strict Implementation Rules
1. **[ID].html:** Keep exact Tailwind classes (`bg-[#282828]`, `text-brand-green`, etc.) and DOM structure from the template. Write exactly 3 Examples with Input, Output, and Explanation.
2. **[ID].py:** - Write `setup_test_data()` to generate randomized, valid test cases.
   - Write `teacher_solve()` with the correct logic.
   - Keep `auto_grade()` logic identical to the template: 20 tests, `try/except` block, deep copy mutable inputs, and exact `wa_mess` formatting.
3. **[ID]_Template.py:** Provide only the empty function signature and a print statement testing Example 1.

## 4. Templates to Follow

### Template 1: HTML
<div class="space-y-4">
    <div>
        <p class="">[PROBLEM_DESCRIPTION_HTML]</p>
        <p> Function return <code>[RETURN_TYPE]</code>.</p>
    </div>
    <div class="grid grid-cols-1 gap-6 mt-4">
        <div>
            <h3 class="text-white font-bold text-sm mb-2">Example 1:</h3>
            <div class="bg-[#282828] border border-gray-700 rounded-lg p-3 font-mono text-sm leading-relaxed">
                <div class="flex gap-2">
                    <span class="text-white font-bold select-none">Input:</span>
                    <span class="text-white">[EXAMPLE_1_INPUT]</span>
                </div>
                <div class="flex gap-2 mt-2">
                    <span class="text-white font-bold select-none">Output:</span>
                    <span class="text-brand-green font-bold">[EXAMPLE_1_OUTPUT]</span>
                </div>
                <div class="text-gray-500 text-xs mt-2 italic">Explanation: [EXAMPLE_1_EXPLANATION]</div>
            </div>
        </div>
        </div>
</div>

### Template 2: Auto-Grader Python
import random
import copy

random.seed(42)

def setup_test_data():
    # TODO: Implement random data generation
    return None

def teacher_solve(input_data):
    # TODO: Implement correct logic
    return None

def auto_grade():
    score = 0
    n_test = 20
    wa_mess = "" 

    for i in range(1, n_test + 1):
        input_data = setup_test_data()
        expected = teacher_solve(input_data)
        student_result = None
        try:
            input_copy = copy.deepcopy(input_data)
            student_result = FUNCTION_NAME_PLACEHOLDER(input_copy) 
        except Exception as e:
            print(f"Test {i} Runtime Error: {e}")
            continue 

        if student_result == expected:
            score += 1
        else:
            if wa_mess == "":
                wa_mess =  (f"-------------------------------\n")
                wa_mess += (f"Example test failed (Test {i}):\n")
                wa_mess += (f"   Input:\n      {input_data}\n")
                wa_mess += (f"   Expected:    {expected}\n")
                wa_mess += (f"   Your Output: {student_result}\n")

    if score == n_test:
        print("Accepted\n" + f"Pass: {score}/{n_test} tests")
    else:
        print("Wrong Answer\n" + f"Pass: {score}/{n_test} tests\n" + wa_mess)

if "FUNCTION_NAME_PLACEHOLDER" in globals():
    auto_grade()
else:
    print("Compile Error: Function 'FUNCTION_NAME_PLACEHOLDER' is not defined.")

### Template 3: Student Template Python
def FUNCTION_NAME_PLACEHOLDER(INPUT_PARAMS_PLACEHOLDER):
    
    return

# Testing your function  
test_input = [EXAMPLE_1_DATA]
print(FUNCTION_NAME_PLACEHOLDER(test_input))