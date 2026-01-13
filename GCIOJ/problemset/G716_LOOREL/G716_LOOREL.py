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
    
    # Save the original Skulpt output handler (which sends to your JS outf function)
    original_stdout = sys.stdout 

    
        
    # Calculate Expected Answer (e.g., Print Max)
    expected_str = teacher_solve()

    # --- START CAPTURE ---
    catcher = OutputCatcher()
    sys.stdout = catcher  # Hijack the print function
    
    try:
        # Run Student Code (Assuming they print the result)
        # You might need to pass arguments depending on the problem type
        solve() 
    except Exception as e:
        sys.stdout = original_stdout
        print(f"Test 1 Failed: Runtime Error -> {e}")
        return
    
    sys.stdout = original_stdout # Restore immediately
    # --- END CAPTURE ---

    # Compare
    student_str = catcher.getvalue().strip()
    
    if is_match_string(student_str,expected_str):
        score += 1
    else:
        # Print specific mismatch info to help student debug
        print(f"Test 1 Failed: Expected '{expected_str}', Got '{student_str}'")

    # Final Result
    if score == n_test:
        print("Accepted")
    else:
        print("Wrong Answer")
        print(f"Final Score: {score}/{n_test}")

# Run
if "solve" in globals():
    auto_grade()
else:
    print("Compile Error: Function 'solve' is not defined.")