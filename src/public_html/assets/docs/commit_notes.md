

## 4th Commit

### Key Components Implemented
#### 1. Middleware Infrastructure
    1. Middleware Interface Structure
        - Adopted PSR-15 standard interfaces from psr/http-server-handler and psr/http-server-middleware
    2. MiddlewarePipeline
        - Implemented a FIFO (First In, First Out) pipeline that chains middleware together
        - Delegates to a fallback handler (FrontController) when middleware stack is exhausted
        - Maintains the request/response flow through the entire application
#### 2. Implemented Several Middleware Classes
    1. TimingMiddleware
        - Created example middleware that measures execution time
        - Adds an X-Execution-Time header to all responses
        - Provides valuable performance metrics without modifying core code
    2. ErrorHandlerMiddleware
        - Integrated exception handling into the middleware flow
        - Catches exceptions from downstream handlers and middleware
        - Delegates to the existing ErrorHandler for consistent error presentation
        - Ensures that other middleware still processes error responses
        - Enhanced Error Handler With Session Support
    3. SessionMiddleware
        - Starts the PHP session
        - Makes session data available to controllers via request attributes
        - Follows the established middleware pattern


#### 3. Modified Core Framework Components
- Updated FrontController to use middleware pipeline
- Enhanced Router to work with PSR-15 middleware
- Added request attribute passing to controller actions
- Improved error handling with proper middleware integration


#### Notes

##### Now: Full middleware pipeline
`Request → MiddlewarePipeline → TimingMiddleware → ErrorHandlerMiddleware → 
SessionMiddleware → FrontController → Router → Controller → Response`
