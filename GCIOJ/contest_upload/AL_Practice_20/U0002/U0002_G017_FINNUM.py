# Write your code for G017_FINNUM here
def solve(number, x, y):
    # Your code goes here
    candidates = [a for a in number if a%x == a%y == 0]
    if len(candidates) == 0: return -1
    return candidates[0]