import React from 'react';
import ReactDOM from 'react-dom';
import {ViewsCarousel} from './index';

it('should mount', () => {
  const div = document.createElement('div');
  ReactDOM.render(<ViewsCarousel sceneViews={[]} handleChange={() => {}}/>, div);
  ReactDOM.unmountComponentAtNode(div);
});
