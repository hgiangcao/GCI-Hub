import sys
import random


# --- 1. Helper Class to Catch Output (Replaces io.StringIO) ---
class OutputCatcher:
    def __init__(self):
        self.text = ""
    def write(self, string):
        self.text += string
    def flush(self): 
        pass 
    def getvalue(self):
        return self.text

# --- 2. Data Generator ---
random.seed(42)

def teacher_solve():
	return "Hello World!"


def is_match_string(a,b):
    return  a == b

# --- 3. Auto Grader ---
def auto_grade():
    score = 0
    n_test = 1  # Reduced for speed
    
    # Save original streams to restore later
    original_stdout = sys.stdout 
    original_stdin = sys.stdin


    expected_str = teacher_solve()

    # B. Mock Environment
    
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
        return # Stop grading
    
    # C. Restore Environment
    sys.stdout = original_stdout

    # D. Smart Output Parsing
    # 1. Get raw output and remove trailing whitespace (newlines at end)
    full_output = catcher.getvalue()
    
    # 2. Split into lines
    all_lines = full_output.split('\n')
    
    # 3. Filter out empty lines (in case of print("") or extra newlines)
    valid_lines = [line.strip() for line in all_lines if line.strip() != ""]
    
    # 4. Get the last valid line
    last_line = valid_lines[-1] if valid_lines else ""
    
    if is_match_string(last_line,expected_str):
        score += 1
    else:
        wa_mess =  (f"-------------------------------\n")
        wa_mess +=  (f"Example test failed:\n")
        wa_mess += (f"   Expected:    Hello World!\n")
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