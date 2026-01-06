import sys

# Import the user's solution
from Solution import Solution

def auto_grade():
    sol = Solution()
    score = 0
    
    # Test Case 1
    if sol.solve([1,2]) == 3:
        score += 50
        
    print(f"Score: {score}")

if __name__ == "__main__":
    auto_grade()