import sys
import random

# --- 1. Helper Classes ---
class OutputCatcher:
    def __init__(self): 
        self.text = ""
    def write(self, string): 
        self.text += string
    def flush(self): 
        pass 
    def getvalue(self): 
        return self.text

class MockInput:
    def __init__(self, inputs):
        self.inputs = inputs
        self.index = 0
    def readline(self):
        # Return the next input if available
        if self.index < len(self.inputs):
            val = self.inputs[self.index]
            self.index += 1
            return str(val)
        return "" # Return empty string if student asks for too many inputs

# --- 2. Data Generator ---
random.seed(42)

# Global variables (so teacher_solve can access them cleanly)
a,b,c = 0,0,0

def is_close_value(a,b):
    return (abs(float(a) - float(b)) < 0.001)

def is_match_string(a,b):
    return (abs(float(a) - float(b)) < 0.001)

def setup_test_data():
    global a,b,c
    a = random.randint(1,100)
    b = random.randint(1,100)
    c = random.randint(1,100)
    return [a, b, c]

def teacher_solve():
    
    return str((a+b+c)/3)

# --- 3. Auto Grader ---
def auto_grade():
    score = 0
    n_test = 20
    
    # Save original streams to restore later
    original_stdout = sys.stdout 
    original_stdin = sys.stdin


    for i in range(1, n_test + 1):
        # A. Setup Data
        inputs = setup_test_data()
        expected_str = teacher_solve()

        # B. Mock Environment
        # Feed inputs to sys.stdin so student's input() reads them
        sys.stdin = MockInput(inputs)
        
        # Capture sys.stdout so we can see what student prints
        catcher = OutputCatcher()
        sys.stdout = catcher 
        
        try:
            # Run the Student Code
            solve() 
        except Exception as e:
            # Restore immediately on crash
            sys.stdout = original_stdout
            sys.stdin = original_stdin
            print(f"Test {i} Runtime Error: {e}")
            return # Stop grading
        
        # C. Restore Environment
        sys.stdout = original_stdout
        sys.stdin = original_stdin

        # D. Smart Output Parsing
        # 1. Get raw output and remove trailing whitespace (newlines at end)
        full_output = catcher.getvalue()
        
        # 2. Split into lines
        all_lines = full_output.split('\n')
        
        # 3. Filter out empty lines (in case of print("") or extra newlines)
        valid_lines = [line.strip() for line in all_lines if line.strip() != ""]
        
        # 4. Get the last valid line
        last_line = valid_lines[-1] if valid_lines else ""

        # E. Comparison
        # Check if the last line ENDS with the expected answer.
        # This allows prompts like "The max is: 50" to pass if expected is "50"
        if is_close_value(expected_str,last_line):
            score += 1
        else:
            wa_mess =  (f"-------------------------------\n")
            wa_mess +=  (f"Example test failed:\n")
            wa_mess += (f"   Input: a = {inputs[0]}; b = {inputs[1]}; c = {inputs[2]}\n")
            wa_mess += (f"   Expected:    {expected_str}\n")
            wa_mess += (f"   Your Output: {last_line}\n")

    # Final Result
    if score == n_test:
        print("Accepted")
        print(f"Pass: {score}/{n_test} tests")
    else:
        print("Wrong Answer")
        print(f"Pass: {score}/{n_test} tests")
        print(wa_mess)

# --- 4. Execution Entry Point ---
if "solve" in globals():
    auto_grade()
else:
    print("Compile Error: Function 'solve' is not defined.")